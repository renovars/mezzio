<?php

use Phpmig\Migration\Migration;

class AddAccountIdColumn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "ALTER TABLE `users` ADD `account_id` INT(10)";
        $container = $this->getContainer();
        $container['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "ALTER TABLE `users` DROP COLUMN `account_id`";
        $container = $this->getContainer();
        $container['db']->query($sql);
    }
}
