<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Payment Link - SingaPay Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Create Payment Link</h1>
            <a href="{{ route('singapay.dashboard') }}"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('singapay.payment-links.create') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account ID</label>
                        <input type="text" name="account_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter the account ID">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" name="reff_no" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value="PL{{ time() }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Payment for Product/Service">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount (IDR)</label>
                        <input type="number" name="total_amount" required min="1000"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="100000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Hours</label>
                        <input type="number" name="expiry_hours" value="24" min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Usage</label>
                        <input type="number" name="max_usage" value="1" min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="required_customer_detail" id="required_customer_detail"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="required_customer_detail" class="ml-2 block text-sm text-gray-900">
                            Require Customer Details
                        </label>
                    </div>
                </div>

                <button type="submit"
                    class="mt-6 w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Create Payment Link
                </button>
            </form>
        </div>
    </div>
</body>

</html>