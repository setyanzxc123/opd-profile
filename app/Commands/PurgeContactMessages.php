<?php

namespace App\Commands;

use App\Models\ContactMessageModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class PurgeContactMessages extends BaseCommand
{
    protected $group       = 'Contacts';
    protected $name        = 'contacts:purge';
    protected $description = 'Hapus atau anonimkan pesan kontak berstatus closed yang sudah usang.';
    protected $usage       = 'contacts:purge [days] [--anonymize]';

    public function run(array $params)
    {
        $days = isset($params[0]) ? (int) $params[0] : 90;
        if ($days < 1) {
            CLI::error('Parameter days harus lebih besar dari 0.');
            return;
        }

        $options    = CLI::getOptions();
        $anonymize  = isset($options['anonymize']);

        $threshold = Time::now('UTC')->subDays($days)->toDateTimeString();
        $model     = model(ContactMessageModel::class);

        $builder = $model->builder();
        $builder
            ->where('status', 'closed')
            ->groupStart()
                ->where('responded_at <', $threshold)
                ->orWhere('responded_at', null)
            ->groupEnd();

        $messages = $builder->get()->getResultArray();
        $total    = count($messages);

        if ($total === 0) {
            CLI::write('Tidak ada pesan closed yang melebihi batas usia.', 'yellow');
            return;
        }

        if ($anonymize) {
            foreach ($messages as $message) {
                $model->update($message['id'], [
                    'email'      => null,
                    'phone'      => null,
                    'user_agent' => null,
                    'ip_address' => null,
                    'admin_note' => trim(($message['admin_note'] ?? '') . '\n[Anonimized ' . Time::now()->toDateTimeString() . ']'),
                ]);
            }
            CLI::write("$total pesan berhasil dianonimkan.", 'green');
            return;
        }

        $ids = array_column($messages, 'id');
        $model->whereIn('id', $ids)->delete();
        CLI::write("$total pesan berhasil dihapus.", 'green');
    }
}

