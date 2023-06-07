<?php

namespace ADP\BaseVersion\Includes\Database\Repository;

interface ThemeModificationsRepositoryInterface
{
    public function getModifications();

    public function drop();

    public function truncate();
}
