<div class="card">
    <div class="card-header">
        <h2>Informações do Workspace</h2>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Nome do Workspace</label>
                <input type="text" name="name" value="<?= htmlspecialchars($currentWorkspace['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Plano Atual</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-primary" style="font-size: 14px; padding: 8px 16px;">
                        <?= ucfirst($currentWorkspace['plan']) ?>
                    </span>
                    <a href="/settings.php?tab=billing" class="btn btn-sm btn-secondary">Gerenciar Plano</a>
                </div>
            </div>
            
            <button type="submit" name="update_workspace" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</div>





