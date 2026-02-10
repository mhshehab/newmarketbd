<div x-show="loginModal" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-md w-full p-8 shadow-2xl relative" @click.away="loginModal = false">
        <button @click="loginModal = false" class="absolute top-4 right-4 text-gray-400 text-2xl hover:text-black">&times;</button>
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-pink-600">Welcome to FreshCart</h2>
            <p class="text-gray-500 text-sm italic mt-1">Please login to access your profile & history</p>
        </div>

        <div class="space-y-4">
            <button class="w-full flex items-center justify-center gap-3 border py-2.5 rounded-lg hover:bg-gray-50 font-bold transition">
                <img src="https://www.svgrepo.com/show/355037/google.svg" class="w-5 h-5" alt="Google"> 
                Continue with Google
            </button>

            <div class="flex items-center gap-2 text-gray-300 text-xs py-2 uppercase font-bold">
                <hr class="flex-1"> or <hr class="flex-1">
            </div>

            <input type="text" placeholder="Mobile Number (e.g. +88017...)" 
                class="w-full border rounded-lg py-3 px-4 outline-none focus:border-pink-500 transition shadow-sm">
            
            <button class="w-full bg-gray-900 text-white py-3 rounded-lg font-bold hover:bg-black transition">
                SEND OTP
            </button>
        </div>
    </div>
</div>