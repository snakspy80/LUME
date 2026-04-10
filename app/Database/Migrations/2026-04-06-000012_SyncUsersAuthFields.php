<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SyncUsersAuthFields extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('phone', 'users')) {
            $fields['phone'] = [
                'type' => 'VARCHAR',
                'constraint' => 25,
                'null' => true,
                'default' => null,
                'after' => 'email',
            ];
        }

        if (! $this->db->fieldExists('email_verified_at', 'users')) {
            $fields['email_verified_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'password',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('users', $fields);
        }

        if ($this->db->fieldExists('phone', 'users')) {
            $this->db->query("UPDATE users SET phone = NULL WHERE TRIM(COALESCE(phone, '')) = ''");
            $this->db->query('ALTER TABLE users MODIFY phone VARCHAR(25) NULL DEFAULT NULL');

            $indexes = $this->db
                ->query("SHOW INDEX FROM users WHERE Column_name = 'phone' AND Non_unique = 0")
                ->getResultArray();

            if ($indexes === []) {
                $this->db->query('ALTER TABLE users ADD UNIQUE KEY users_phone_unique (phone)');
            }
        }
    }

    public function down()
    {
        // Non-destructive rollback by design.
    }
}
