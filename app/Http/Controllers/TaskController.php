<?

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller {


    // Get tasks with optional filters
    public function index(Request $request) {
        $query = Task::where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        return response()->json($query->get(), 200);
    }

    // Create a task
    public function store(Request $request) {
        $this->validate($request, [
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'status'      => 'required|in:To Do,In Progress,Done',
            'due_date'    => 'required|date',
        ]);

        $task = Task::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->status,
            'due_date'    => $request->due_date,
        ]);

        return response()->json($task, 201);
    }

    // Update a task
    public function update(Request $request, $id) {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->update($request->only(['name', 'description', 'status', 'due_date']));

        return response()->json($task, 200);
    }

    // Delete a task
    public function destroy($id) {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
