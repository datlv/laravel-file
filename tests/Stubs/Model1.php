<?php namespace Datlv\File\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Datlv\File\Support\HasFile;

/**
 * Class Model1
 * @property integer $id
 * @property string $name
 * @property integer $file_id
 * @package Datlv\File\Tests\Stubs
 * @author Minh Bang
 */
class Model1 extends Model
{
    use HasFile;

    public $table = 'filetest_model1s';
    public $timestamps = false;
    protected $fillable = ['name', 'file_id'];
}