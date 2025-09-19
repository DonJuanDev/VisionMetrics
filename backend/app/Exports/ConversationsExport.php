<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ConversationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $conversations;

    public function __construct(Collection $conversations)
    {
        $this->conversations = $conversations;
    }

    public function collection()
    {
        return $this->conversations;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Lead Nome',
            'Lead Telefone',
            'Lead Origem',
            'Status',
            'Responsável',
            'Total Mensagens',
            'Total Conversões',
            'Valor Total',
            'Iniciada em',
            'Última Atividade',
            'Data Criação',
        ];
    }

    public function map($conversation): array
    {
        return [
            $conversation->id,
            $conversation->lead->name ?? 'N/A',
            $conversation->lead->phone ?? 'N/A',
            $this->formatOrigin($conversation->lead->origin),
            $this->formatStatus($conversation->status),
            $conversation->assignedAgent->name ?? 'Não atribuído',
            $conversation->messages_count ?? 0,
            $conversation->conversions_count ?? 0,
            $this->formatTotalValue($conversation),
            $conversation->started_at ? $conversation->started_at->format('d/m/Y H:i') : '',
            $conversation->last_activity_at ? $conversation->last_activity_at->format('d/m/Y H:i') : '',
            $conversation->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 25,  // Lead Nome
            'C' => 20,  // Lead Telefone
            'D' => 15,  // Lead Origem
            'E' => 15,  // Status
            'F' => 25,  // Responsável
            'G' => 15,  // Total Mensagens
            'H' => 15,  // Total Conversões
            'I' => 15,  // Valor Total
            'J' => 18,  // Iniciada em
            'K' => 18,  // Última Atividade
            'L' => 18,  // Data Criação
        ];
    }

    private function formatOrigin($origin)
    {
        return match($origin) {
            'meta' => 'Meta Ads',
            'google' => 'Google Ads',
            'outras' => 'Outras Origens',
            'nao_rastreada' => 'Não Rastreada',
            default => $origin ?? 'N/A',
        };
    }

    private function formatStatus($status)
    {
        return match($status) {
            'open' => 'Aberta',
            'closed' => 'Fechada',
            'qualified' => 'Qualificada',
            'converted' => 'Convertida',
            'lost' => 'Perdida',
            default => $status ?? 'N/A',
        };
    }

    private function formatTotalValue($conversation)
    {
        // Se a relação conversions não estiver carregada, retorna valor padrão
        if (!isset($conversation->conversions)) {
            return 'R$ 0,00';
        }

        $totalValue = $conversation->conversions
            ->where('status', 'confirmed')
            ->sum('value');

        if (!$totalValue || $totalValue <= 0) {
            return 'R$ 0,00';
        }

        return 'R$ ' . number_format($totalValue, 2, ',', '.');
    }
}
