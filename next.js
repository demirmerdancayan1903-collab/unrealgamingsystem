import React from 'react'

export default function UnrealConnectButton() {
  return (
    <a 
      href="https://unrealconnect.onrender.com" 
      target="_blank" 
      rel="noopener noreferrer"
      className="
        flex items-center justify-center gap-2
        w-[180px] h-[36px] 
        bg-gradient-to-r from-[#39c766] to-[#2eb059] 
        text-black font-bold text-sm rounded-lg
        shadow-[0_0_15px_rgba(57,199,102,0.3)]
        hover:shadow-[0_0_25px_rgba(57,199,102,0.6)]
        hover:scale-[1.03] active:scale-[0.98]
        transition-all duration-300 ease-out
        tracking-wide select-none cursor-pointer
      "
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
        <path d="M1.42 7.57a18.4 18.4 0 0 1 21.16 0"></path>
        <path d="M8.58 17.55a5 5 0 0 1 6.84 0"></path>
        <line x1="12" y1="20" x2="12.01" y2="20"></line>
      </svg>
      <span>Unreal Connect</span>
    </a>
  )
}
