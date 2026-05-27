<div class="flex-1 max-w-xl mx-4 hidden sm:block" x-data="{ 
        searchQuery: '', 
        suggestions: [], 
        showSuggestions: false,
        loading: false 
    }">
    <!-- Search Form -->
    <form action="{{ route('search') }}" method="GET" class="relative group">
        <div class="relative">
            <input 
                type="text" 
                name="q" 
                x-model="searchQuery"
                @input.debounce.150ms="handleSearch"
                @focus="showSuggestions = true; handleSearch()"
                @blur="setTimeout(() => showSuggestions = false, 300)"
                @keydown.escape="showSuggestions = false"
                placeholder="Search for products (e.g. eggs, milk, আলু)" 
                class="w-full bg-gray-100 border-none rounded-md py-2.5 px-10 focus:ring-2 focus:ring-pink-500 focus:bg-white transition-all italic"
                autocomplete="off">
            
            <!-- Search Icon -->
            <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400 group-focus-within:text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <!-- Suggestions Dropdown -->
        <div x-show="showSuggestions && suggestions.length > 0" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-y-auto">
            
            <!-- Loading State -->
            <div x-show="loading" class="p-4 text-center">
                <div class="inline-flex items-center">
                    <svg class="animate-spin h-5 w-5 text-pink-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-gray-600">Searching...</span>
                </div>
            </div>

            <!-- Suggestions List -->
            <template x-for="suggestion in suggestions" :key="suggestion.id">
                <a :href="suggestion.url" 
                   class="flex items-center p-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0">
                    
                    <!-- Icon -->
                    <div class="flex-shrink-0 mr-3">
                        <template x-if="suggestion.type === 'product'">
                            <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M20 7l-8-4-8 4m0 6l8 4m8-4v10a2 2 0 01-2 2H6a2 2 0 01-2-2V9m8 4V5a2 2 0 00-2-2H6a2 2 0 00-2 2v4m8 4v6a2 2 0 002 2h2a2 2 0 002-2v-6"/>
                            </svg>
                        </template>
                        <template x-if="suggestion.type === 'category'">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </template>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="font-medium text-gray-800" x-text="suggestion.name"></div>
                                <div class="text-xs text-gray-500">
                                    <template x-if="suggestion.type === 'product'">
                                        Product
                                    </template>
                                    <template x-if="suggestion.type === 'category'">
                                        Category
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Price (only for products) -->
                            <div x-show="suggestion.price" class="ml-3 text-right">
                                <div class="text-sm font-bold text-pink-600" x-text="'BDT ' + suggestion.price"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </template>

            <!-- No Results -->
            <div x-show="!loading && suggestions.length === 0 && searchQuery.length > 0" 
                 class="p-4 text-center text-gray-500">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p>No results found</p>
            </div>
        </div>
    </form>

    <!-- JavaScript for Search -->
    <script>
        function handleSearch() {
            const query = this.searchQuery.trim();
            
            if (query.length < 1) {
                this.suggestions = [];
                this.showSuggestions = false;
                this.loading = false;
                return;
            }

            this.loading = true;
            
            // Cancel previous request if exists
            if (this.searchAbortController) {
                this.searchAbortController.abort();
            }
            
            // Create new abort controller
            this.searchAbortController = new AbortController();
            
            fetch(`/search/autocomplete?q=${encodeURIComponent(query)}`, {
                signal: this.searchAbortController.signal
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    this.suggestions = data;
                    this.loading = false;
                    this.showSuggestions = true;
                })
                .catch(error => {
                    if (error.name !== 'AbortError') {
                        console.error('Search error:', error);
                        this.suggestions = [];
                        this.loading = false;
                    }
                });
        }
    </script>
</div>