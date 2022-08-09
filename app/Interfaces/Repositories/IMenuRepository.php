<?php

namespace App\Interfaces\Repositories;

interface IMenuRepository
{
    public function createOrUpdate($data);
    public function deleteMenu($date);
    public function getMenu();
    public function getMenuById($id);
    public function getMenuByDate($date);
}
