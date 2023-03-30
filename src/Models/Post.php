<?php

namespace App\Models;

class Post extends Model
{
    protected string $table = 'posts';

    public function homePosts()
    {
        return $this
            ->where([
                'posts.status' => 'published',
                'posts.published_at[<=]' => today(),
            ])
            ->join([
                '[><]departments' => ['department_id' => 'id'],
            ])
            ->select([
                'posts.id',
                'posts.title',
                'posts.body',
                'posts.image',
                'posts.published_at',
                'department' => [
                    'departments.id',
                    'departments.name',
                ],
            ]);
    }
}
