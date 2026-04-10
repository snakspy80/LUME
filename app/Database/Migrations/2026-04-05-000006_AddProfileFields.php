<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfileFields extends Migration
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
                'after' => 'email',
            ];
        }

        if (! $this->db->fieldExists('avatar', 'users')) {
            $fields['avatar'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'bio',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        // Non-destructive rollback by design.
    }
}
