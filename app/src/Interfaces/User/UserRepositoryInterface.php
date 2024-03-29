<?php
namespace TriTan\Interfaces\User;

use TriTan\Common\User\User;

interface UserRepositoryInterface
{
    public function findById(int $id);
    public function findBy(string $field, $value);
    public function findBySql(string $fields, $where = '');
    public function findAll();
    public function insert(User $user);
    public function update(User $user);
    public function delete(User $user);
}
