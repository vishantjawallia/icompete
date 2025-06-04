<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f5f5f5;
        }

        .status-container {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .success-container {
            border-top: 8px solid #22c55e;
        }

        .error-container {
            border-top: 8px solid #ef4444;
        }

        .icon-circle {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.3s ease-in-out;
        }

        .success-circle {
            background: #dcfce7;
            color: #22c55e;
        }

        .error-circle {
            background: #fee2e2;
            color: #ef4444;
        }

        .icon-circle i {
            font-size: 48px;
            animation: iconScale 0.5s ease-in-out;
        }

        .status-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .status-message {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .details {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-size: 0.875rem;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: #6b7280;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-success {
            background: #22c55e;
            color: white;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .btn-error {
            background: #ef4444;
            color: white;
        }

        .btn-error:hover {
            background: #dc2626;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes iconScale {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Success State -->
    <div class="status-container success-container">
        <div class="icon-circle success-circle">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="status-title">Payment Successful!</h1>
        <p class="status-message">Your payment has been processed successfully.
            <br> The coins have been added to your account.</p>
        @if($message)
        <div class="details">
            <div class="detail-item">
                <span style="color: #16a34a;">{{$message}}</span>
            </div>
            {{--<div class="detail-item">
                <span class="detail-label">Amount Paid</span>
                <span>$99.99</span>
            </div> --}}
        </div>
        @endif
        {{-- <a href="#" class="btn btn-success">Back to Home</a> --}}
    </div>

</body>
</html>
