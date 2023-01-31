<?php

use Phpmig\Migration\Migration;

class CreateUserTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "CREATE TABLE `users` (`id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                      `name` VARCHAR(30), 
                                      `base_domain` VARCHAR(255),
                                      `client_id` VARCHAR(255),
                                      `client_secret` VARCHAR(255),
                                      `redirect_uri` VARCHAR(255),
                                      `access_token` JSON,
                                      `api_key` VARCHAR(255))";
        $container = $this->getContainer();
        $container['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "DROP TABLE `users`";
        $container = $this->getContainer();
        $container['db']->query($sql);
    }
}
