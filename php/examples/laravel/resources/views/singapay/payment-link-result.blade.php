<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Link Created - SingaPay Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-4">Payment Link Created Successfully!</h1>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-600 mb-2">Payment URL:</p>
                <a href="{{ $paymentLink['payment_url'] }}" target="_blank"
                    class="text-blue-600 hover:text-blue-800 break-all">
                    {{ $paymentLink['payment_url'] }}
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6 text-left">
                <div>
                    <p class="text-sm text-gray-600">Reference Number:</p>
                    <p class="font-semibold">{{ $paymentLink['reff_no'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Amount:</p>
                    <p class="font-semibold">IDR {{ number_format($paymentLink['total_amount'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="flex gap-4 justify-center">
                <a href="{{ $paymentLink['payment_url'] }}" target="_blank"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Open Payment Link
                </a>
                <a href="{{ route('singapay.payment-links') }}"
                    class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    Create Another
                </a>
                <a href="{{ route('singapay.dashboard') }}"
                    class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</body>

</html>