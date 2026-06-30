<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Face Attendance</title>
    <!-- Load face-api.js from a CDN or local assets -->
    <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1/dist/face-api.min.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .container { max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        #preview { max-width: 100%; margin-top: 10px; display: none; }
        #status { margin-top: 15px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>Register Face for Attendance</h2>
    <p>Select a staff member and upload a clear photo of their face to generate their attendance embedding.</p>

    <div class="form-group">
        <label for="staff_id">Staff ID:</label>
        <input type="number" id="staff_id" required>
    </div>

    <div class="form-group">
        <label for="photo">Upload Clear Photo:</label>
        <input type="file" id="photo" accept="image/*">
    </div>

    <img id="preview" src="" alt="Face Preview">
    <div id="status">Please upload a photo...</div>

    <button id="registerBtn" style="display:none;" onclick="registerFace()">Register Face Embedding</button>
</div>

<script>
    let extractedEmbedding = null;

    // Load Models on startup
    async function loadModels() {
        document.getElementById('status').innerText = 'Loading ML models...';
        // Note: You must host the face-api models folder in your public directory
        // For demonstration, we assume they are in /public/models/
        // Download models from: https://github.com/justadudewhohacks/face-api.js/tree/master/weights
        try {
            await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
            document.getElementById('status').innerText = 'Models loaded! Ready to process photo.';
        } catch (e) {
            document.getElementById('status').innerText = 'Error loading models. Ensure /models directory exists in public/.';
        }
    }

    // Process uploaded image
    document.getElementById('photo').addEventListener('change', async (event) => {
        const file = event.target.files[0];
        if (!file) return;

        const imgElement = document.getElementById('preview');
        imgElement.src = URL.createObjectURL(file);
        imgElement.style.display = 'block';
        
        document.getElementById('status').innerText = 'Detecting face...';
        document.getElementById('registerBtn').style.display = 'none';

        try {
            // Convert to HTMLImageElement format face-api expects
            const img = await faceapi.bufferToImage(file);
            
            // Detect single face and get descriptors
            const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();

            if (!detection) {
                document.getElementById('status').innerText = 'No face detected! Please use a clearer photo.';
                extractedEmbedding = null;
                return;
            }

            // Convert Float32Array to standard JS Array
            extractedEmbedding = Array.from(detection.descriptor);
            document.getElementById('status').innerText = 'Face detected and embedding generated successfully!';
            document.getElementById('registerBtn').style.display = 'block';

        } catch (error) {
            console.error(error);
            document.getElementById('status').innerText = 'Error processing image.';
        }
    });

    async function registerFace() {
        const staffId = document.getElementById('staff_id').value;
        if (!staffId || !extractedEmbedding) {
            alert('Staff ID and a processed photo are required.');
            return;
        }

        document.getElementById('status').innerText = 'Saving to database...';

        try {
            const response = await fetch('/admin/attendance/register-face', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'staff_id': staffId,
                    'embedding': JSON.stringify(extractedEmbedding)
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                document.getElementById('status').innerText = 'Success: ' + result.message;
                document.getElementById('registerBtn').style.display = 'none';
            } else {
                document.getElementById('status').innerText = 'Error: ' + (result.message || 'Unknown error');
            }
        } catch (e) {
            document.getElementById('status').innerText = 'Network error saving embedding.';
        }
    }

    // Initialize
    loadModels();
</script>

</body>
</html>
