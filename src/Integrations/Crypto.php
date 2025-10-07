<?php
/**
 * VisionMetrics - Integration Credentials Encryption
 * 
 * Provides AES-256-GCM encryption/decryption for sensitive integration credentials
 * stored in database. Uses INTEGRATIONS_KEY from environment.
 * 
 * Security features:
 * - AES-256-GCM (authenticated encryption)
 * - Unique nonce per encryption
 * - Authentication tag verification
 * - Base64 encoding for database storage
 * 
 * @package VisionMetrics\Integrations
 */

namespace VisionMetrics\Integrations;

class Crypto
{
    private const CIPHER = 'aes-256-gcm';
    private const NONCE_LENGTH = 12; // 96 bits recommended for GCM
    private const TAG_LENGTH = 16; // 128 bits
    
    /**
     * Get encryption key from environment
     * 
     * @return string Binary encryption key
     * @throws \RuntimeException If key is not configured
     */
    private static function getKey(): string
    {
        $key = getenv('INTEGRATIONS_KEY');
        
        if (empty($key)) {
            throw new \RuntimeException(
                'INTEGRATIONS_KEY not configured in environment. ' .
                'Generate with: php -r "echo base64_encode(random_bytes(32));"'
            );
        }
        
        // Support both base64 and hex encoded keys
        if (base64_decode($key, true) !== false && strlen(base64_decode($key, true)) === 32) {
            return base64_decode($key);
        }
        
        if (ctype_xdigit($key) && strlen($key) === 64) {
            return hex2bin($key);
        }
        
        // Assume raw key (must be exactly 32 bytes)
        if (strlen($key) !== 32) {
            throw new \RuntimeException(
                'INTEGRATIONS_KEY must be exactly 32 bytes (256 bits). ' .
                'Current length: ' . strlen($key) . ' bytes'
            );
        }
        
        return $key;
    }
    
    /**
     * Encrypt plaintext using AES-256-GCM
     * 
     * @param string $plaintext Data to encrypt
     * @return string Base64-encoded encrypted data (nonce + ciphertext + tag)
     * @throws \RuntimeException On encryption failure
     */
    public static function encrypt(string $plaintext): string
    {
        if (empty($plaintext)) {
            throw new \InvalidArgumentException('Cannot encrypt empty plaintext');
        }
        
        $key = self::getKey();
        
        // Generate random nonce (NEVER reuse with same key)
        $nonce = random_bytes(self::NONCE_LENGTH);
        
        // Encrypt with authentication tag
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '', // additional authenticated data (AAD) - can add workspace_id if needed
            self::TAG_LENGTH
        );
        
        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
        }
        
        // Package: nonce + ciphertext + tag
        $package = $nonce . $ciphertext . $tag;
        
        // Base64 encode for safe storage in database TEXT column
        return base64_encode($package);
    }
    
    /**
     * Decrypt ciphertext using AES-256-GCM
     * 
     * @param string $encrypted Base64-encoded encrypted data
     * @return string Decrypted plaintext
     * @throws \RuntimeException On decryption or authentication failure
     */
    public static function decrypt(string $encrypted): string
    {
        if (empty($encrypted)) {
            throw new \InvalidArgumentException('Cannot decrypt empty ciphertext');
        }
        
        $key = self::getKey();
        
        // Decode from base64
        $package = base64_decode($encrypted, true);
        
        if ($package === false) {
            throw new \RuntimeException('Invalid base64 encoding');
        }
        
        // Extract components
        $nonce = substr($package, 0, self::NONCE_LENGTH);
        $tag = substr($package, -self::TAG_LENGTH);
        $ciphertext = substr($package, self::NONCE_LENGTH, -self::TAG_LENGTH);
        
        if (strlen($nonce) !== self::NONCE_LENGTH || strlen($tag) !== self::TAG_LENGTH) {
            throw new \RuntimeException('Corrupted encrypted data: invalid component lengths');
        }
        
        // Decrypt and verify authentication tag
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );
        
        if ($plaintext === false) {
            throw new \RuntimeException(
                'Decryption failed: Authentication tag mismatch or corrupted data'
            );
        }
        
        return $plaintext;
    }
    
    /**
     * Encrypt JSON data (helper for credentials)
     * 
     * @param array $data Associative array to encrypt
     * @return string Base64-encoded encrypted JSON
     */
    public static function encryptJson(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($json === false) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }
        
        return self::encrypt($json);
    }
    
    /**
     * Decrypt JSON data (helper for credentials)
     * 
     * @param string $encrypted Base64-encoded encrypted JSON
     * @return array Decrypted associative array
     */
    public static function decryptJson(string $encrypted): array
    {
        $json = self::decrypt($encrypted);
        
        $data = json_decode($json, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON decoding failed: ' . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Test encryption/decryption roundtrip (for setup verification)
     * 
     * @return bool True if working correctly
     */
    public static function selfTest(): bool
    {
        try {
            $original = 'Test credentials: ' . uniqid();
            $encrypted = self::encrypt($original);
            $decrypted = self::decrypt($encrypted);
            
            return $original === $decrypted;
        } catch (\Exception $e) {
            error_log('Crypto self-test failed: ' . $e->getMessage());
            return false;
        }
    }
}




