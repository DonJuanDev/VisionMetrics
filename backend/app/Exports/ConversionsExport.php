<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ConversionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $conversions;

    public function __construct(Collection $conversions)
    {
        $this->conversions = $conversions;
    }

    public function collection()
    {
        return $this->conversions;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Lead Nome',
            'Lead Telefone',
            'Origem',
            'UTM Source',
            'UTM Campaign',
            'Valor',
            'Moeda',
            'Método Pagamento',
            'Status',
            'Detectado por',
            'Confirmado por',
            'Data Detecção',
            'Data Confirmação',
            'Observações',
        ];
    }

    public function map($conversion): array
    {
        return [
            $conversion->id,
            $conversion->conversation->lead->name ?? 'N/A',
            $conversion->conversation->lead->phone ?? 'N/A',
            $this->formatOrigin($conversion->conversation->lead->origin),
            $conversion->conversation->lead->utm_source ?? '',
            $conversion->conversation->lead->utm_campaign ?? '',
            $this->formatCurrency($conversion->value),
            $conversion->currency,
            $this->formatPaymentMethod($conversion->payment_method),
            $this->formatStatus($conversion->status),
            $this->formatDetectedBy($conversion->detected_by),
            $conversion->confirmedBy->name ?? 'N/A',
            $conversion->detected_at ? $conversion->detected_at->format('d/m/Y H:i') : '',
            $conversion->confirmed_at ? $conversion->confirmed_at->format('d/m/Y H:i') : '',
            $conversion->notes ?? '',
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
            'B' => 25,  // Nome
            'C' => 20,  // Telefone
            'D' => 15,  // Origem
            'E' => 15,  // UTM Source
            'F' => 20,  // UTM Campaign
            'G' => 15,  // Valor
            'H' => 10,  // Moeda
            'I' => 20,  // Método Pagamento
            'J' => 15,  // Status
            'K' => 15,  // Detectado por
            'L' => 20,  // Confirmado por
            'M' => 18,  // Data Detecção
            'N' => 18,  // Data Confirmação
            'O' => 30,  // Observações
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

    private function formatPaymentMethod($method)
    {
        return match($method) {
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'transferencia' => 'Transferência',
            'dinheiro' => 'Dinheiro',
            'outro' => 'Outro',
            default => $method ?? 'N/A',
        };
    }

    private function formatStatus($status)
    {
        return match($status) {
            'pending' => 'Pendente',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            default => $status ?? 'N/A',
        };
    }

    private function formatDetectedBy($detectedBy)
    {
        return match($detectedBy) {
            'manual' => 'Manual',
            'nlp' => 'Automático (NLP)',
            'webhook' => 'Webhook',
            default => $detectedBy ?? 'N/A',
        };
    }

    private function formatCurrency($value)
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}
