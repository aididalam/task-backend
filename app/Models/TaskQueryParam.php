<?

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskQueryParam extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'query_params'];
    public $timestamps = false;

    protected $casts = [
        'query_params' => 'array', // Automatically cast JSON to an array
    ];
}
