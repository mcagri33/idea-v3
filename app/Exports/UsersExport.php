<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Export edilecek kullanıcılar
     */
    public function collection()
    {
        return User::with('roles')->select('id', 'name', 'email', 'status', 'created_at')->get();
    }

    /**
     * Başlık satırları
     */
    public function headings(): array
    {
        return ['ID', 'Ad', 'E-posta', 'Rol(ler)', 'Durum', 'Oluşturulma Tarihi'];
    }

    /**
     * Verileri biçimlendirme (rolleri dahil etme)
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '), 
            $user->status == 1 ? 'Aktif' : 'Pasif',
            $user->created_at->format('Y-m-d H:i'),
        ];
    }
}
