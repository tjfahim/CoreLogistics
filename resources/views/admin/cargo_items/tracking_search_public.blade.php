<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment | Courier Service</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #7209b7;
            --light-bg: #f8fafc;
            --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --card-radius: 16px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .track-container {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
        }
        
        .track-card {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            padding: 40px 35px;
            position: relative;
            transition: transform 0.3s ease;
        }
        
        .track-card:hover {
            transform: translateY(-5px);
        }
        
        .track-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .track-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.25);
        }
        
        .track-icon i {
            font-size: 36px;
            color: white;
        }
        
        .track-header h1 {
            font-weight: 700;
            color: #1e293b;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .track-header p {
            color: #64748b;
            font-size: 16px;
            font-weight: 400;
        }
        
        .alert-custom {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            border: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .alert-danger i {
            font-size: 18px;
        }
        
        .track-form {
            margin-bottom: 28px;
        }
        
        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 5px;
        }
        
        .tracking-input {
            height: 58px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 17px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            color: #1e293b;
            letter-spacing: 0.5px;
        }
        
        .tracking-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
        }
        
        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }
        
        .track-btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            height: 58px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 17px;
            letter-spacing: 0.3px;
            margin-top: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .track-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), #6511a6);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(33, 49, 118, 0.25);
        }
        
        .example-section {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 18px;
            margin-top: 25px;
            text-align: center;
        }
        
        .example-title {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .tracking-example {
            background: white;
            border-radius: 8px;
            padding: 12px 16px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 16px;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-top: 5px;
        }
        
        .footer-note {
            text-align: center;
            margin-top: 28px;
            color: #94a3b8;
            font-size: 14px;
        }
        
        .footer-note a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer-note a:hover {
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .track-card {
                padding: 30px 25px;
            }
            
            .track-header h1 {
                font-size: 24px;
            }
            
            .tracking-input, .track-btn {
                height: 54px;
            }
        }
        
        /* Animation for focus effect */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(67, 97, 238, 0); }
            100% { box-shadow: 0 0 0 0 rgba(67, 97, 238, 0); }
        }
        
        .pulse-effect {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>

<body>
    <div class="track-container">
        <div class="track-card">
            <div class="track-header">
                <div class="track-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h1>Track Your Shipment</h1>
                <p>Enter your tracking number to check the status of your package</p>
            </div>
            
            @if(session('error'))
                <div class="alert-custom alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            
            <form action="{{ route('cargo-items.tracking-view') }}" method="POST" class="track-form">
                @csrf
                
                <label class="form-label">
                    <i class="fas fa-barcode"></i> Tracking Number
                </label>
                
                <div class="input-group">
                    <input type="text"
                           name="tracking_no"
                           class="form-control tracking-input"
                           placeholder=""
                           required
                           style="border:1px solid #203850ff;"
                           >
                         
                    <span class="input-icon">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 track-btn">
                    <i class="fas fa-satellite-dish"></i> Track Shipment
                </button>
            </form>
            
            <div class="example-section">
                <div class="example-title">
                    <i class="fas fa-info-circle"></i> Example Tracking Number
                </div>
                <div class="tracking-example">ZT78934768097756</div>
                <p class="mt-3 mb-0" style="color: #64748b; font-size: 14px;">
                    Enter a 16-digit tracking number like the example above
                </p>
            </div>
            
            <div class="footer-note">
                <p>Need help? <a href="#">Contact our support team</a> or <a href="#">view tracking FAQ</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add subtle interaction effects
        document.addEventListener('DOMContentLoaded', function() {
            const trackingInput = document.querySelector('.tracking-input');
            const trackBtn = document.querySelector('.track-btn');
            
            // Add focus effect to input
            trackingInput.addEventListener('focus', function() {
                this.parentElement.classList.add('pulse-effect');
            });
            
            trackingInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('pulse-effect');
            });
            
            // Button click animation
            trackBtn.addEventListener('click', function() {
                if(trackingInput.value.trim() === '') {
                    trackingInput.focus();
                }
            });
            
            // Auto-select example number when clicked
            const exampleNumber = document.querySelector('.tracking-example');
            exampleNumber.addEventListener('click', function() {
                trackingInput.value = this.textContent;
                trackingInput.focus();
                trackingInput.select();
            });
        });
    </script>
</body>
</html>