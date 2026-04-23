<div class="min-h-screen w-full flex items-center justify-center" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);">
    <style>
        .login-input {
            display: block !important;
            width: 100% !important;
            padding: 12px 16px !important;
            border: 1.5px solid #d1fae5 !important;
            border-radius: 10px !important;
            background-color: #f9fffe !important;
            color: #111827 !important;
            font-size: 15px !important;
            font-family: inherit !important;
            outline: none !important;
            box-sizing: border-box !important;
            box-shadow: none !important;
            -webkit-text-fill-color: #111827 !important;
            transition: border-color 0.2s !important;
        }
        .login-input:focus {
            border-color: #16a34a !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.12) !important;
        }
        .login-input::placeholder {
            color: #9ca3af !important;
            -webkit-text-fill-color: #9ca3af !important;
            opacity: 1 !important;
        }
        .login-input:-webkit-autofill,
        .login-input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0px 1000px #f9fffe inset !important;
            -webkit-text-fill-color: #111827 !important;
        }
    </style>

    <div class="w-full px-6" style="max-width: 420px;">

        {{-- Card --}}
        <div class="bg-white p-8 rounded-2xl" style="box-shadow: 0 8px 40px rgba(0,0,0,0.08); border: 1px solid #d1fae5;">

            {{-- Logo + Nama sejajar --}}
            <div class="flex items-center gap-4 mb-8 pb-6" style="border-bottom: 1.5px solid #d1fae5;">
                <div class="flex-shrink-0 flex items-center justify-center w-16 h-16 rounded-2xl" style="background:#16a34a; box-shadow: 0 4px 16px rgba(22,163,74,0.3);">
                    <svg width="34" height="34" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 55 Q20 35 40 28 Q60 35 60 55" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"/>
                        
                        <line x1="40" y1="55" x2="40" y2="65" stroke="white" stroke-width="4" stroke-linecap="round"/>
                        <path d="M20 62 Q30 57 40 65 Q50 57 60 62" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-black text-green-800" style="font-size: 22px; letter-spacing:-0.5px; line-height:1.2;">SiTani</h1>
                    <p class="text-green-600" style="font-size: 13px; margin-top: 2px;">Irigasi Cerdas, Panen Berkualitas</p>
                </div>
            </div>

            {{-- Form --}}
            <div class="flex flex-col gap-4">
                <div>
                    <input
                        type="email"
                        wire:model="email"
                        placeholder="Alamat Email"
                        class="login-input"
                    />
                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <input
                        type="password"
                        wire:model="password"
                        placeholder="Password"
                        class="login-input"
                    />
                </div>

                <button
                    wire:click="authenticate"
                    class="w-full text-white font-semibold text-sm rounded-xl cursor-pointer mt-1"
                    style="background:#16a34a; border:none; padding: 12px 24px; box-shadow: 0 4px 12px rgba(22,163,74,0.3);"
                    onmouseover="this.style.background='#15803d'"
                    onmouseout="this.style.background='#16a34a'"
                >
                    Masuk
                </button>
            </div>
        </div>

        <p class="text-center text-xs text-green-500 mt-5">© 2026 SiTani. All rights reserved.</p>
    </div>
</div>
