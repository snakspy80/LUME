<?php

namespace App\Models;

use CodeIgniter\Model;

class PostModel extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'title', 'category', 'content', 'video_url', 'video_file', 'is_published'];
    protected $useTimestamps = true;
}
