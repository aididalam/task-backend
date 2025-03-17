<?

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskQueryParam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use WebSocket\Client;

class TaskController extends Controller {


    // Get tasks with optional filters
    public function index(Request $request) {
        $this->_storeQueryParams($request);  // Store query parameters in the database

        // Apply the stored query parameters to the query
        $query = $this->_applyStoredQueryParams();

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

        $item = Task::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->status,
            'due_date'    => $request->due_date,
        ]);

        $query = $this->_applyStoredQueryParams();

        // Get the filtered results
        $tasks = $query->get();
        $task = $query->where('id', $item->id)->get();

        $this->sendWebSocketMessage(json_encode([
            'type' => 'task_added',
            'task' => $task
        ]));

        return response()->json($tasks, 201);
    }

    // Update a task
    public function update(Request $request, $id) {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->update($request->only(['name', 'description', 'status', 'due_date']));

        $this->sendWebSocketMessage(json_encode([
            'type' => 'task_update',
            'task' => $task
        ]));

        return response()->json($task, 200);
    }

    // Delete a task
    public function destroy($id) {
        $task = Task::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        $this->sendWebSocketMessage(json_encode([
            'type' => 'task_delete',
            'task' => $task
        ]));

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }


    private function _storeQueryParams(Request $request) {

        // Convert query params to JSON format
        $queryParams = [
            'q'         => $request->input('q'),
            'startDate' => $request->input('startDate'),
            'endDate'   => $request->input('endDate'),
            'statuses'  => $request->input('statuses')
        ];

        // Store query parameters in TaskQueryParam model
        TaskQueryParam::updateOrCreate(
            ['user_id' => Auth::id()],
            ['query_params' => json_encode($queryParams)]
        );
    }

    private function _applyStoredQueryParams() {
        // Retrieve the stored query parameters for the user
        $query = Task::where('user_id', Auth::id());
        $storedQueryParams = TaskQueryParam::where('user_id', Auth::id())->first();
        // If no query parameters are found, return the original query
        if (!$storedQueryParams) {
            return $query;
        }

        // Decode the stored query parameters from JSON
        $queryParams = json_decode($storedQueryParams->query_params, true);

        // Apply filters based on the stored query parameters
        if (!empty($queryParams['q'])) {
            $search = $queryParams['q'];
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('due_date', 'like', "%{$search}%");
            });
        }

        if (!empty($queryParams['startDate'])) {
            $query->where('due_date', '>=', $queryParams['startDate']);
        }

        if (!empty($queryParams['endDate'])) {
            $query->where('due_date', '<=', $queryParams['endDate']);
        }

        if (!empty($queryParams['statuses'])) {
            $statuses = explode(',', $queryParams['statuses']);
            $query->whereIn('status', $statuses);
        }

        return $query;
    }


    private function sendWebSocketMessage($message) {
        try {
            $client = new Client("ws://localhost:8080"); // Connect using WebSocket

            // Check if the connection is successful
            if ($client->isConnected()) {
                $client->send($message);
                $client->close();
            }
        } catch (Exception $e) {
            // Optionally log the error, but nothing is done if the WebSocket server is unavailable
            // error_log($e->getMessage()); // Uncomment to log the error
        }
    }
}