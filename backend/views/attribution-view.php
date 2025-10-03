<?php
// Available models
$models = [
    'first_touch' => 'First Touch',
    'last_touch' => 'Last Touch',
    'linear' => 'Linear',
    'time_decay' => 'Time Decay',
    'position_based' => 'Position-Based',
    'last_non_direct' => 'Last Non-Direct'
];

// Calculate attribution using the handler
include __DIR__ . '/../handlers/attribution-handler.php';
?>

<div class="card">
    <div class="card-body">
        <label style="font-weight: 600; margin-bottom: 12px; display: block;">Modelo de Atribuição:</label>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php foreach ($models as $key => $name): ?>
                <a href="/analytics.php?tab=attribution&model=<?= $key ?>" 
                   class="btn btn-sm <?= $selectedModel === $key ? 'btn-primary' : 'btn-secondary' ?>">
                    <?= $name ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><?= $models[$selectedModel] ?></h2>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Origem</th>
                    <th>Conversões</th>
                    <th>Valor Atribuído</th>
                    <th>% do Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attributionData)): ?>
                    <tr>
                        <td colspan="4" class="text-center">Nenhuma conversão encontrada</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $totalValue = array_sum(array_column($attributionData, 'value'));
                    foreach ($attributionData as $row): 
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['source']) ?></strong></td>
                            <td><?= $row['conversions'] ?></td>
                            <td class="text-success"><strong><?= formatCurrency($row['value']) ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="flex: 1; height: 8px; background: #F3F4F6; border-radius: 4px; overflow: hidden;">
                                        <div style="height: 100%; background: #4F46E5; width: <?= ($row['value'] / $totalValue * 100) ?>%;"></div>
                                    </div>
                                    <span style="min-width: 50px; font-weight: 600;"><?= number_format($row['value'] / $totalValue * 100, 1) ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>





