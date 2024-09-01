<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    // Display the main reservation view
    public function index()
    {
        return view('reservation');
    }

    // Display reservation details based on the selected date
    public function details(Request $request)
    {
        $reservations = Reservation::all();
        $selectedDate = $request->input('date');
        return view('reservation-details', compact('reservations', 'selectedDate'));
    }

    // Show the reservation form with selected table and date
    public function form(Request $request)
    {
        $selectedTable = $request->query('selectedTable');
        $selectedDate = $request->query('date');
        return view('reservation-form', compact('selectedTable', 'selectedDate'));
    }

    // Handle form submission for a new reservation
    public function submit(Request $request)
    {
        // Log that the submit method has been called
        Log::info('Submit method called.');

        // Log the input data for debugging purposes
        Log::info('Input data: ' . json_encode($request->all()));

        // Validate the request data
        $validated = $request->validate([
            'selectedTable' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'contact' => 'required|string',
            'guests' => 'required|integer|min:1',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'date' => 'required|date', 
        ], [
            'screenshot.required' => 'A screenshot of payment is required.',
            'screenshot.image' => 'The uploaded file must be an image.',
            'screenshot.mimes' => 'Only jpeg, png, jpg, gif, and svg images are allowed.',
            'screenshot.max' => 'The screenshot size should not exceed 2MB.',
        ]);

        // Store the screenshot and generate the path
        $screenshotPath = $validated['screenshot']->store('screenshots', 'public');

        // Store the reservation in the database
        Reservation::create([
            'status' => 'Pending',
            'user_id' => auth()->id(), // Ensure user is authenticated before submitting
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'],
            'table_number' => $validated['selectedTable'],
            'guests' => $validated['guests'],
            'payment_reference' => $request->input('refNo'), // Fetch refNo from request if provided
            'screenshot' => $screenshotPath,
            'date' => $validated['date'], 
        ]);

        // Redirect to the reservation index with success message
        return redirect()->route('reservation.index')->with('success', 'Reservation submitted successfully! Your table is now pending.');
    }

    // Update the reservation status
    public function updateStatus(Request $request)
    {
        $reservation = Reservation::find($request->input('reservationId'));
    
        if ($reservation) {
            // Update the status
            $reservation->status = $request->input('status');
            $reservation->save();
    
            // Check if the status is changed to 'Completed'
            if ($reservation->status === 'Completed') {
                // After marking as completed, remove it from the current reservations view
                return redirect()->route('admin.reservations')->with('success', 'Reservation status updated to Completed and moved to Reports!');
            }
    
            return redirect()->route('admin.reservations')->with('success', 'Reservation status updated successfully!');
        }
    
        return redirect()->route('reservation.index')->with('error', 'Reservation not found.');
    }

    public function delete($id)
    {
        $reservation = Reservation::find($id);

        if ($reservation) {
            $reservation->delete();
            return redirect()->route('admin.reservations')->with('success', 'Reservation deleted successfully!');
        }

        return redirect()->route('admin.reservations')->with('error', 'Reservation not found.');
    }
    

}

