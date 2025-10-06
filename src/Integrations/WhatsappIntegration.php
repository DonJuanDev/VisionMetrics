<?php
/**
 * VisionMetrics - WhatsApp Integration Model
 * 
 * Manages WhatsApp integrations per workspace with encrypted credentials
 * Supports multiple BSP providers (360dialog, Infobip, Twilio, Cloud API, etc)
 * 
 * @package VisionMetrics\Integrations
 */

namespace VisionMetrics\Integrations;

use PDO;

class WhatsappIntegration
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Create or update WhatsApp integration for workspace
     * 
     * @param int $workspaceId Workspace ID
     * @param string $provider Provider name (e.g. '360dialog', 'infobip')
     * @param array $credentials Credentials array (will be encrypted)
     * @param string|null $name Optional user-friendly name
     * @param array|null $meta Optional metadata (phone_id, business_number, etc)
     * @return int Integration ID
     */
    public function createOrUpdate(
        int $workspaceId,
        string $provider,
        array $credentials,
        ?string $name = null,
        ?array $meta = null
    ): int {
        // Encrypt credentials before storage
        $encryptedCredentials = Crypto::encryptJson($credentials);
        
        // Prepare metadata
        $metaJson = $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;
        
        // Check if integration already exists
        $stmt = $this->db->prepare("
            SELECT id FROM whatsapp_integrations 
            WHERE workspace_id = ? AND provider = ?
        ");
        $stmt->execute([$workspaceId, $provider]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing integration
            $stmt = $this->db->prepare("
                UPDATE whatsapp_integrations 
                SET credentials = ?, 
                    name = ?, 
                    meta = ?,
                    status = 'inactive',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $encryptedCredentials,
                $name,
                $metaJson,
                $existing['id']
            ]);
            
            return (int)$existing['id'];
        }
        
        // Create new integration
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_integrations 
            (workspace_id, provider, name, credentials, meta, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'inactive', NOW(), NOW())
        ");
        $stmt->execute([
            $workspaceId,
            $provider,
            $name,
            $encryptedCredentials,
            $metaJson
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Get integration by ID
     * 
     * @param int $integrationId Integration ID
     * @return array|null Integration data (credentials still encrypted)
     */
    public function getById(int $integrationId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_integrations WHERE id = ?
        ");
        $stmt->execute([$integrationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Get integration for workspace by provider
     * 
     * @param int $workspaceId Workspace ID
     * @param string $provider Provider name
     * @return array|null Integration data
     */
    public function getByWorkspaceAndProvider(int $workspaceId, string $provider): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_integrations 
            WHERE workspace_id = ? AND provider = ?
        ");
        $stmt->execute([$workspaceId, $provider]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Get all integrations for workspace
     * 
     * @param int $workspaceId Workspace ID
     * @return array Array of integrations
     */
    public function getByWorkspace(int $workspaceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_integrations 
            WHERE workspace_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$workspaceId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get decrypted credentials for integration
     * 
     * @param int $integrationId Integration ID
     * @return array Decrypted credentials
     * @throws \RuntimeException If integration not found or decryption fails
     */
    public function getCredentialsDecrypted(int $integrationId): array
    {
        $integration = $this->getById($integrationId);
        
        if (!$integration) {
            throw new \RuntimeException("Integration not found: $integrationId");
        }
        
        try {
            return Crypto::decryptJson($integration['credentials']);
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Failed to decrypt credentials for integration $integrationId: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Set integration status
     * 
     * @param int $integrationId Integration ID
     * @param string $status Status: 'inactive', 'active', 'error'
     * @return bool Success
     */
    public function setStatus(int $integrationId, string $status): bool
    {
        $validStatuses = ['inactive', 'active', 'error'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        
        $stmt = $this->db->prepare("
            UPDATE whatsapp_integrations 
            SET status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $integrationId]);
    }
    
    /**
     * Update integration metadata
     * 
     * @param int $integrationId Integration ID
     * @param array $meta Metadata to merge/update
     * @return bool Success
     */
    public function updateMeta(int $integrationId, array $meta): bool
    {
        $integration = $this->getById($integrationId);
        
        if (!$integration) {
            return false;
        }
        
        // Merge with existing meta
        $existingMeta = $integration['meta'] ? json_decode($integration['meta'], true) : [];
        $newMeta = array_merge($existingMeta, $meta);
        
        $stmt = $this->db->prepare("
            UPDATE whatsapp_integrations 
            SET meta = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        return $stmt->execute([
            json_encode($newMeta, JSON_UNESCAPED_UNICODE),
            $integrationId
        ]);
    }
    
    /**
     * Delete integration (soft - mark inactive)
     * 
     * @param int $integrationId Integration ID
     * @param int $workspaceId Workspace ID (for security check)
     * @return bool Success
     */
    public function delete(int $integrationId, int $workspaceId): bool
    {
        // Verify ownership
        $integration = $this->getById($integrationId);
        
        if (!$integration || $integration['workspace_id'] != $workspaceId) {
            return false;
        }
        
        // Hard delete (cascade will remove sessions)
        $stmt = $this->db->prepare("
            DELETE FROM whatsapp_integrations 
            WHERE id = ? AND workspace_id = ?
        ");
        
        return $stmt->execute([$integrationId, $workspaceId]);
    }
    
    /**
     * Create session record for integration
     * 
     * @param int $integrationId Integration ID
     * @param string|null $sessionId BSP session ID
     * @param string|null $qrImageUrl QR code image URL/base64
     * @return int Session ID
     */
    public function createSession(
        int $integrationId,
        ?string $sessionId = null,
        ?string $qrImageUrl = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_sessions 
            (integration_id, session_id, qr_image_url, status, created_at, updated_at)
            VALUES (?, ?, ?, 'pending', NOW(), NOW())
        ");
        $stmt->execute([
            $integrationId,
            $sessionId,
            $qrImageUrl
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Get session by ID
     * 
     * @param int $sessionId Session DB ID
     * @return array|null Session data
     */
    public function getSessionById(int $sessionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, i.workspace_id 
            FROM whatsapp_sessions s
            JOIN whatsapp_integrations i ON s.integration_id = i.id
            WHERE s.id = ?
        ");
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Get session by BSP session_id
     * 
     * @param string $sessionId BSP session ID
     * @return array|null Session data
     */
    public function getSessionBySessionId(string $sessionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, i.workspace_id 
            FROM whatsapp_sessions s
            JOIN whatsapp_integrations i ON s.integration_id = i.id
            WHERE s.session_id = ?
        ");
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
    
    /**
     * Update session status
     * 
     * @param int $sessionId Session DB ID
     * @param string $status Status: 'pending', 'connected', 'disconnected', 'error'
     * @param string|null $errorMessage Optional error message
     * @return bool Success
     */
    public function updateSessionStatus(
        int $sessionId,
        string $status,
        ?string $errorMessage = null
    ): bool {
        $validStatuses = ['pending', 'connected', 'disconnected', 'error'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        
        $stmt = $this->db->prepare("
            UPDATE whatsapp_sessions 
            SET status = ?, 
                error_message = ?,
                last_heartbeat = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $errorMessage, $sessionId]);
    }
    
    /**
     * Get active session for integration
     * 
     * @param int $integrationId Integration ID
     * @return array|null Active session data
     */
    public function getActiveSession(int $integrationId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_sessions 
            WHERE integration_id = ? 
            AND status IN ('pending', 'connected')
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$integrationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}

