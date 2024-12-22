<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-4xl font-bold">Welcome, {{ auth()->user()->name }}!</h1>
                    <p class="mt-4">You are successfully logged in.</p>
                </div>
            </div>
        </div>

        <div class="mt-2 flex justify-center">
            <button id="addItemButton" class="bg-blue-500 text-white px-4 py-2 rounded">Add New Item</button>
        </div>

        <!-- Centralize Table -->
        <div class="mt-5 flex justify-center">
            <table class="table-auto border-collapse border border-gray-400 w-3/4">
                <thead>
                    <tr>
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Name</th>
                        <th class="border border-gray-300 px-4 py-2">Created At</th>
                    </tr>
                </thead>
                <tbody id="itemsTable">
                    <!-- Items will be dynamically added here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="addItemModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-5 rounded shadow">
            <h2 class="text-xl mb-3">Add New Item</h2>
            <form id="addItemForm">
                <div class="mb-3">
                    <label for="itemName" class="block text-sm">Item Name:</label>
                    <input type="text" id="itemName" name="itemName" class="w-full border px-3 py-2">
                    <small class="text-red-500 hidden" id="itemNameError">Item name is required.</small>
                </div>
                <div class="text-right">
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Save</button>
                    <button type="button" id="closeModalButton" class="bg-red-500 text-white px-4 py-2 rounded">Close</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const addItemButton = document.getElementById('addItemButton');
    const addItemModal = document.getElementById('addItemModal');
    const closeModalButton = document.getElementById('closeModalButton');
    const addItemForm = document.getElementById('addItemForm');
    const itemNameInput = document.getElementById('itemName');
    const itemNameError = document.getElementById('itemNameError');
    const itemsTable = document.getElementById('itemsTable');

    // Show modal
    addItemButton.addEventListener('click', () => {
        addItemModal.classList.remove('hidden');
    });

    // Hide modal
    closeModalButton.addEventListener('click', () => {
        addItemModal.classList.add('hidden');
    });
    const formatDate = (date) => {
        return moment(date).format('DD-MM-YY');
    };
    // Fetch and display items
    const fetchItems = () => {
        axios.get('/api/items')
            .then(response => {
                itemsTable.innerHTML = '';
                response.data.forEach(item => {
                    const formattedDate = formatDate(item.created_at);
                    const row = `<tr>
                        <td class="border border-gray-300 px-4 py-2">${item.id}</td>
                        <td class="border border-gray-300 px-4 py-2">${item.name}</td>
                        <td class="border border-gray-300 px-4 py-2">${formattedDate}</td>
                    </tr>`;
                    itemsTable.innerHTML += row;
                });
            })
            .catch(error => console.error(error));
    };

    fetchItems();

    // Submit form with AJAX
    addItemForm.addEventListener('submit', (e) => {
        e.preventDefault();

        // Clear previous error message
        itemNameError.classList.add('hidden');

        // Get the item name
        const itemName = itemNameInput.value.trim();

        // Client-side validation: Check if name is empty
        if (!itemName) {
            itemNameError.classList.remove('hidden');
            itemNameError.textContent = 'Item name is required.';
            return;
        }

        // Proceed with form submission
        axios.post('/api/items', {
            name: itemName
        })
        .then(response => {
            // Refresh items table
            fetchItems();
            // Hide modal
            addItemModal.classList.add('hidden');
            // Reset form
            addItemForm.reset();
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                // Check if the error is due to the unique name validation
                const errors = error.response.data.errors;
                if (errors && errors.name) {
                    itemNameError.classList.remove('hidden');
                    itemNameError.textContent = errors.name[0]; // Display the unique name error
                }
            }
            console.error(error);
        });
    });
});

</script>
