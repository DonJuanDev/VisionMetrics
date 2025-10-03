<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; gap: 8px;">
        <a href="/automation.php?tab=tasks&task_filter=pending" class="btn btn-sm <?= ($_GET['task_filter'] ?? 'pending') === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pendentes</a>
        <a href="/automation.php?tab=tasks&task_filter=completed" class="btn btn-sm <?= ($_GET['task_filter'] ?? '') === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Concluídas</a>
        <a href="/automation.php?tab=tasks&task_filter=all" class="btn btn-sm <?= ($_GET['task_filter'] ?? '') === 'all' ? 'btn-primary' : 'btn-secondary' ?>">Todas</a>
    </div>
    <button onclick="showCreateTaskModal()" class="btn btn-primary">+ Nova Tarefa</button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tarefa</th>
                    <th>Lead</th>
                    <th>Responsável</th>
                    <th>Prioridade</th>
                    <th>Prazo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="empty-state" style="padding: 40px;">
                                <div class="empty-icon">✅</div>
                                <p>Nenhuma tarefa encontrada</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($task['title']) ?></strong></td>
                            <td><?= $task['lead_name'] ? htmlspecialchars($task['lead_name']) : '-' ?></td>
                            <td><?= $task['assigned_name'] ?? 'Não atribuída' ?></td>
                            <td>
                                <span class="badge badge-<?= match($task['priority']) { 'urgent' => 'danger', 'high' => 'warning', default => 'info' } ?>">
                                    <?= ucfirst($task['priority']) ?>
                                </span>
                            </td>
                            <td><?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-' ?></td>
                            <td>
                                <?php if ($task['status'] !== 'completed'): ?>
                                    <a href="/automation.php?tab=tasks&complete_task=<?= $task['id'] ?>" class="btn btn-sm btn-success">Concluir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showCreateTaskModal() {
    const title = prompt('Título da tarefa:');
    if (title) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/automation.php?tab=tasks';
        form.innerHTML = `
            <input type="hidden" name="create_task" value="1">
            <input type="hidden" name="title" value="${title}">
            <input type="hidden" name="priority" value="medium">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>





