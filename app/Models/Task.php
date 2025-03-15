<?

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model {
    protected $table = 'tasks';
    protected $fillable = ['user_id', 'name', 'description', 'status', 'due_date'];
    public $timestamps = false;

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
