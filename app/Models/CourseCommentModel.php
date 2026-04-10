<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseCommentModel extends Model
{
    protected $table = 'course_comments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['post_id', 'user_id', 'parent_id', 'type', 'content'];
    protected $useTimestamps = true;
}
