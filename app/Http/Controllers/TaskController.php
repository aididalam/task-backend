<?

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller {


    // Get tasks with optional filters
    public function index(Request $request) {
        // Initialize the query for tasks based on the user ID
        $query = Task::where('user_id', Auth::id());

        // Handle the search filter (search in 'name', 'status', 'due_date')
        if ($request->has('q') && $request->input('q') != '') {
            $search = $request->input('q');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('due_date', 'like', "%{$search}%"); // Search in 'due_date' as well
            });
        }

        // Handle the startDate filter (for filtering tasks after a certain due_date)
        if ($request->has('startDate') && $request->input('startDate') != '') {
            $startDate = $request->input('startDate');
            $query->where('due_date', '>=', $startDate); // Filter tasks where 'due_date' is greater than or equal to startDate
        }

        // Handle the endDate filter (for filtering tasks before a certain due_date)
        if ($request->has('endDate') && $request->input('endDate') != '') {
            $endDate = $request->input('endDate');
            $query->where('due_date', '<=', $endDate); // Filter tasks where 'due_date' is less than or equal to endDate
        }

        // Handle the status filter (optional, if status is provided)
        if ($request->has('statuses') && !empty($request->input('statuses'))) {
            $statuses = explode(',', $request->input('statuses')); // Assumes 'statuses' is passed as a comma-separated string
            $query->whereIn('status', $statuses); // Adjust column name 'status' as per your model
        }

        // Get the filtered results
        $tasks = $query->get();

        // Return the results as JSON
        return response()->json($tasks, 200);
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
