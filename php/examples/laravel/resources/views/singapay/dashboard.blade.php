<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SingaPay Demo - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">SingaPay Integration Demo</h1>

        <!-- Connection Status -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Connection Status</h2>
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full mr-2 {{ $connectionTest['success'] ? 'bg-green-500' : 'bg-red-500' }}">
                </div>
                <span class="{{ $connectionTest['success'] ? 'text-green-600' : 'text-red-600' }}">
                    {{ $connectionTest['success'] ? 'Connected to SingaPay API' : 'Connection Failed: ' .
                    $connectionTest['message'] }}
                </span>
            </div>
        </div>

        <!-- Navigation -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('singapay.accounts') }}"
                class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-lg font-semibold mb-2">Account Management</h3>
                <p class="text-gray-600">Create and manage merchant accounts</p>
            </a>

            <a href="{{ route('singapay.payment-links') }}"
                class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-lg font-semibold mb-2">Payment Links</h3>
                <p class="text-gray-600">Create payment links for customers</p>
            </a>

            <a href="{{ route('singapay.virtual-accounts') }}"
                class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-lg font-semibold mb-2">Virtual Accounts</h3>
                <p class="text-gray-600">Generate virtual account numbers</p>
            </a>

            <a href="{{ route('singapay.disbursement') }}"
                class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-lg font-semibold mb-2">Disbursement Tools</h3>
                <p class="text-gray-600">Check beneficiaries and fees</p>
            </a>
        </div>

        <!-- SDK Metrics -->
        @if(!empty($metrics))
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">SDK Metrics</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $metrics['total_requests'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total Requests</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $metrics['successful_requests'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Successful</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $metrics['failed_requests'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Failed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($metrics['total_response_time'] ??
                        0, 2) }}s</div>
                    <div class="text-sm text-gray-600">Response Time</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</body>

</html>