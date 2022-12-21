<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Profile extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        ($this->table("profiles"))->addColumn("fullname", "string")
            ->addColumn("photo", "string")
            ->addColumn("gender", "enum", ["values" => ["l", "p"]])
            ->addColumn("nisn", "string")
            ->addColumn("birth_of_place", "string")
            ->addColumn("birthday", "date")
            ->addColumn("religion", "string")
            ->addColumn("address", "string")
            ->addColumn("phone", "string")
            ->addColumn("school_origin", "string")
            ->addColumn("parent_status", "enum", ["values" => ["mother", "father", "guard"]])
            ->addColumn("parent_name", "string")
            ->addColumn("parent_nik", "string")
            ->addColumn("kk_number", "string")
            ->addColumn("parent_place_of_birth", "string")
            ->addColumn("parent_birthday", "date")
            ->addColumn("profession", "string")
            ->addColumn("income", "integer")
            ->addColumn("parent_address", "string")
            ->addColumn("user_id", "integer", ["signed" => false])
            ->addTimestamps()
            ->addForeignKey("user_id", "users", "id", ["delete" => "CASCADE", "update" => "CASCADE"])
            ->create();
    }
}
