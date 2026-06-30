<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FaceEmbeddingModel;
use App\Libraries\FaceRecognition;

class FaceAttendanceController extends ResourceController
{
    protected $format = 'json';

    /**
     * Renders the Web UI for registering a face.
     */
    public function index()
    {
        return view('admin/attendance/register_face');
    }

    /**
     * Endpoint for the Web Admin panel to register a new face embedding.
     * Expected POST Payload:
     * - staff_id (int)
     * - embedding (JSON string array)
     */
    public function registerEmbedding()
    {
        $staffId = $this->request->getPost('staff_id');
        $embeddingJson = $this->request->getPost('embedding');

        if (!$staffId || !$embeddingJson) {
            return $this->failValidationError('Staff ID and Embedding are required.');
        }

        $embeddingModel = new FaceEmbeddingModel();

        // Check if an embedding already exists for this staff
        $existing = $embeddingModel->where('staff_id', $staffId)->first();
        
        if ($existing) {
            $embeddingModel->update($existing['id'], [
                'embedding' => $embeddingJson
            ]);
            return $this->respondUpdated(['message' => 'Face embedding updated successfully.']);
        } else {
            $embeddingModel->insert([
                'staff_id'  => $staffId,
                'embedding' => $embeddingJson
            ]);
            return $this->respondCreated(['message' => 'Face embedding registered successfully.']);
        }
    }

    /**
     * Endpoint for the Mobile App to mark attendance using a face embedding.
     * Expected POST Payload:
     * - staff_id (int)
     * - embedding (JSON string array)
     */
    public function markAttendance()
    {
        $staffId = $this->request->getPost('staff_id');
        $capturedEmbeddingJson = $this->request->getPost('embedding');

        if (!$staffId || !$capturedEmbeddingJson) {
            return $this->failValidationError('Staff ID and captured embedding are required.');
        }

        $capturedEmbedding = json_decode($capturedEmbeddingJson, true);

        if (!is_array($capturedEmbedding)) {
            return $this->failValidationError('Invalid embedding format.');
        }

        $embeddingModel = new FaceEmbeddingModel();
        $storedData = $embeddingModel->where('staff_id', $staffId)->first();

        if (!$storedData) {
            return $this->failNotFound('No face registered for this staff member.');
        }

        $storedEmbedding = json_decode($storedData['embedding'], true);

        // Calculate Cosine Similarity
        try {
            $similarity = FaceRecognition::cosineSimilarity($capturedEmbedding, $storedEmbedding);
        } catch (\Exception $e) {
            return $this->failServerError('Error calculating similarity: ' . $e->getMessage());
        }

        // Define a threshold (e.g., 0.85 for Cosine Similarity, depending on the ML model)
        $threshold = 0.85;

        if ($similarity >= $threshold) {
            // Similarity matches, mark attendance
            
            // NOTE: Assuming the 'staff_attendance' table is used
            $db = \Config\Database::connect();
            $attendanceDate = date('Y-m-d');
            
            // Check if already checked in today
            $existingAttendance = $db->table('staff_attendance')
                                     ->where('staff_id', $staffId)
                                     ->where('attendance_date', $attendanceDate)
                                     ->get()->getRowArray();
                                     
            if ($existingAttendance) {
                // If they already checked in, maybe update check-out time
                $db->table('staff_attendance')
                   ->where('id', $existingAttendance['id'])
                   ->update(['check_out_time' => date('H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
                
                return $this->respond(['status' => 'success', 'message' => 'Check-out successful. Similarity: ' . round($similarity, 2)]);
            } else {
                // First time checking in today
                $db->table('staff_attendance')->insert([
                    'staff_id' => $staffId,
                    'attendance_date' => $attendanceDate,
                    'status' => 'Present',
                    'check_in_time' => date('H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_by' => $staffId
                ]);

                return $this->respond(['status' => 'success', 'message' => 'Check-in successful. Similarity: ' . round($similarity, 2)]);
            }

        } else {
            return $this->fail('Face mismatch. Verification failed. Similarity: ' . round($similarity, 2), 403);
        }
    }
}
