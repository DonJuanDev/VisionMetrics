<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LeadsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $leads;

    public function __construct(Collection $leads)
    {
        $this->leads = $leads;
    }

    public function collection()
    {
        return $this->leads;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Telefone',
            'Email',
            'Origem',
            'UTM Source',
            'UTM Campaign',
            'UTM Medium',
            'UTM Term',
            'UTM Content',
            'Token Rastreamento',
            'Status',
            'Tags',
            'Total Conversões',
            'Valor Total',
            'Primeiro Contato',
            'Última Mensagem',
            'Data Cadastro',
        ];
    }

    public function map($lead): array
    {
        $totalConversions = $lead->conversions->where('status', 'confirmed')->count();
        $totalValue = $lead->conversions->where('status', 'confirmed')->sum('value');

        return [
            $lead->id,
            $lead->name ?? 'N/A',
            $lead->phone ?? 'N/A',
            $lead->email ?? '',
            $this->formatOrigin($lead->origin),
            $lead->utm_source ?? '',
            $lead->utm_campaign ?? '',
            $lead->utm_medium ?? '',
            $lead->utm_term ?? '',
            $lead->utm_content ?? '',
            $lead->tracking_token ?? '',
            $this->formatStatus($lead->status),
            $this->formatTags($lead->tags),
            $totalConversions,
            $this->formatCurrency($totalValue),
            $lead->first_contact_at ? $lead->first_contact_at->format('d/m/Y H:i') : '',
            $lead->last_message_at ? $lead->last_message_at->format('d/m/Y H:i') : '',
            $lead->created_at->format('d/m/Y H:i'),
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
            'D' => 25,  // Email
            'E' => 15,  // Origem
            'F' => 15,  // UTM Source
            'G' => 20,  // UTM Campaign
            'H' => 15,  // UTM Medium
            'I' => 15,  // UTM Term
            'J' => 15,  // UTM Content
            'K' => 20,  // Token
            'L' => 15,  // Status
            'M' => 20,  // Tags
            'N' => 15,  // Total Conversões
            'O' => 15,  // Valor Total
            'P' => 18,  // Primeiro Contato
            'Q' => 18,  // Última Mensagem
            'R' => 18,  // Data Cadastro
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
            'new' => 'Novo',
            'contacted' => 'Contatado',
            'qualified' => 'Qualificado',
            'converted' => 'Convertido',
            'lost' => 'Perdido',
            default => $status ?? 'N/A',
        };
    }

    private function formatTags($tags)
    {
        if (empty($tags)) {
            return '';
        }

        return is_array($tags) ? implode(', ', $tags) : $tags;
    }

    private function formatCurrency($value)
    {
        if (!$value || $value <= 0) {
            return 'R$ 0,00';
        }

        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}
