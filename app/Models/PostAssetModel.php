<?php

namespace App\Models;

use CodeIgniter\Model;

class PostAssetModel extends Model
{
    protected $table = 'post_assets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['post_id', 'user_id', 'file_path', 'file_name', 'mime_type', 'file_size', 'asset_kind'];
    protected $useTimestamps = true;
}
