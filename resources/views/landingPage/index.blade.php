<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<title>MeetFlow — Meeting Management</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="web_standard" name="shell-type"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-container-highest": "#d3e4fe",
            "on-secondary-container": "#fefcff",
            "tertiary-fixed": "#e9ddff",
            "surface": "#f8f9ff",
            "surface-container-high": "#dce9ff",
            "secondary-fixed-dim": "#adc6ff",
            "on-surface-variant": "#45464c",
            "surface-container-low": "#eff4ff",
            "outline-variant": "#c6c6cd",
            "background": "#f8f9ff",
            "on-primary": "#ffffff",
            "inverse-on-surface": "#eaf1ff",
            "on-secondary-fixed": "#001a42",
            "secondary-fixed": "#d8e2ff",
            "on-primary-fixed": "#141b2b",
            "surface-dim": "#cbdbf5",
            "primary-fixed": "#dce2f7",
            "on-primary-fixed-variant": "#404758",
            "on-surface": "#0b1c30",
            "on-error-container": "#93000a",
            "primary-container": "#111827",
            "on-tertiary-fixed": "#23005c",
            "on-primary-container": "#7d8497",
            "primary": "#111827",
            "on-background": "#0b1c30",
            "tertiary-fixed-dim": "#d0bcff",
            "primary-fixed-dim": "#c0c6db",
            "on-secondary-fixed-variant": "#004395",
            "tertiary-container": "#23005c",
            "error-container": "#ffdad6",
            "on-tertiary-container": "#9466ff",
            "on-tertiary-fixed-variant": "#5516be",
            "secondary-container": "#2170e4",
            "error": "#ba1a1a",
            "on-error": "#ffffff",
            "surface-variant": "#d3e4fe",
            "inverse-surface": "#213145",
            "surface-bright": "#f8f9ff",
            "surface-container-lowest": "#ffffff",
            "surface-container": "#e5eeff",
            "on-tertiary": "#ffffff",
            "inverse-primary": "#c0c6db",
            "tertiary": "#000000",
            "outline": "#76777d",
            "surface-tint": "#575e70",
            "on-secondary": "#ffffff",
            "secondary": "#0058be"
          },
          fontFamily: {
            sans: ["Inter", "sans-serif"]
          }
        }
      }
    };
  </script>
<style>
    @layer base {
      html { scroll-behavior: smooth; font-family: 'Inter', sans-serif; }
      body { overscroll-behavior: none; }
    }
  </style>
</head>
<body class="bg-[#F8FAFC] font-sans text-slate-900 antialiased selection:bg-blue-600 selection:text-white">
<!-- 1. NAVIGATION BAR -->
<header class="sticky top-0 z-50 w-full bg-white/80 backdrop-blur-md border-b border-slate-200/80 transition-all">
<div class="max-w-7xl mx-auto px-6 lg:px-12 h-16 flex items-center justify-between">
<!-- Logo -->
<a class="flex items-center gap-2.5 group" data-path="landing-page" href="#">
<div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-sm shadow-blue-500/20 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined text-[20px]">event_repeat</span>
</div>
<span class="text-xl font-bold tracking-tight text-slate-900">MeetFlow</span>
</a>
<!-- Center Links (TaskGo Style) -->
<nav class="hidden md:flex items-center gap-8 text-[14px] font-medium text-slate-600">
<a class="hover:text-blue-600 transition-colors" data-path="features" href="#features">Features</a>
<a class="hover:text-blue-600 transition-colors" data-path="how-it-works" href="#how-it-works">How It Works</a>
<a class="hover:text-blue-600 transition-colors" data-path="ai-assistant" href="#ai-assistant">AI Assistant</a>
<a class="hover:text-blue-600 transition-colors" data-path="pricing" href="#">Pricing</a>
</nav>
<!-- Right CTAs -->
<div class="flex items-center gap-4">
<a class="text-[14px] font-semibold text-slate-700 hover:text-slate-900 px-2 py-1.5 transition-colors" data-path="sign-in" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">Sign In</a>
<a class="inline-flex items-center justify-center px-4 py-2 text-[14px] font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/30 transition-all hover:shadow" data-path="get-started" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">
          Get Started
        </a>
</div>
</div>
</header>
<main class="w-full overflow-hidden">
<!-- 2. HERO SECTION (TaskGo Centered Layout + Atmospheric Ambient Wash) -->
<section class="relative w-full pt-16 pb-16 lg:pt-24 lg:pb-24">
<!-- Ambient Cloud / Soft Sky Atmospheric Gradient -->
<div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_80%_60%_at_50%_0%,rgba(219,234,254,0.7),rgba(243,232,255,0.4),rgba(248,250,252,0))] pointer-events-none"></div>
<!-- Floating subtle badges (TaskGo style atmospheric tags) -->
<div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 border border-slate-200/60 shadow-sm backdrop-blur-xs absolute top-28 left-[12%] text-[12px] font-medium text-slate-600 animate-pulse">
<span class="w-2 h-2 rounded-full bg-blue-500"></span>
<span>Product Team</span>
</div>
<div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 border border-slate-200/60 shadow-sm backdrop-blur-xs absolute top-36 right-[13%] text-[12px] font-medium text-slate-600">
<span class="w-2 h-2 rounded-full bg-purple-500"></span>
<span>AI Workspace Active</span>
</div>
<div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col items-center text-center">
<!-- Eyebrow Pill -->
<div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/90 border border-blue-200 text-blue-700 shadow-xs mb-6 backdrop-blur-sm">
<span class="material-symbols-outlined text-[15px]">auto_awesome</span>
<span class="text-[11px] font-bold tracking-wider uppercase">AI-POWERED MEETING MANAGEMENT</span>
</div>
<!-- Headline: Centered, Large, Two-line rhythm -->
<h1 class="text-4xl sm:text-5xl lg:text-[62px] font-extrabold tracking-tight text-slate-900 leading-[1.12] max-w-4xl mb-6">
          Make Every Meeting<br class="hidden sm:inline"/> More Productive.
        </h1>
<!-- Supporting text -->
<p class="text-lg sm:text-xl text-slate-600 max-w-2xl leading-relaxed mb-8">
          Plan meetings, organize discussions, and turn meeting notes into actionable tasks with the help of AI.
        </p>
<!-- Centered Dual CTAs -->
<div class="flex flex-wrap items-center justify-center gap-3.5 mb-7 w-full sm:w-auto">
<a class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[15px] shadow-lg shadow-blue-600/25 transition-all group" data-path="get-started" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">
<span>Get Started</span>
<span class="material-symbols-outlined text-[18px] transition-transform group-hover:translate-x-1">arrow_forward</span>
</a>
<a class="inline-flex items-center justify-center px-7 py-3.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-[15px] shadow-xs transition-colors" href="#features">
<span>Explore Features</span>
</a>
</div>
<!-- Trust Microcopy & Rating (TaskGo Style Centered Trust Bar) -->
<div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-6 text-slate-600 mb-12 sm:mb-16">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[18px] text-blue-600">verified</span>
<span class="text-[13px] font-medium text-slate-700">Organize your meetings. Clarify your decisions. Keep your team moving.</span>
</div>
<div class="hidden sm:block w-1 h-1 rounded-full bg-slate-300"></div>
<div class="flex items-center gap-2.5">
<div class="flex -space-x-1.5">
<div class="w-6 h-6 rounded-full bg-slate-200 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-slate-700">JD</div>
<div class="w-6 h-6 rounded-full bg-blue-100 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-blue-700">SK</div>
<div class="w-6 h-6 rounded-full bg-purple-100 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-purple-700">AL</div>
<div class="w-6 h-6 rounded-full bg-amber-100 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-amber-700">MR</div>
</div>
<div class="flex items-center gap-1">
<div class="flex items-center text-amber-500">
<span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
<span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
</div>
<span class="text-[12px] font-semibold text-slate-700">Trusted by 1,200+ product teams</span>
</div>
</div>
</div>
<!-- PRODUCT UI MOCKUP (MEEEASY Dashboard Aesthetic + TaskGo Overlapping Hero Display) -->
<div class="w-full max-w-6xl relative">
<!-- Ambient Glows -->
<div class="absolute -top-12 left-1/4 w-96 h-96 bg-blue-400/15 rounded-full blur-3xl -z-10 pointer-events-none"></div>
<div class="absolute -bottom-10 right-1/4 w-96 h-96 bg-purple-400/15 rounded-full blur-3xl -z-10 pointer-events-none"></div>
<div class="rounded-2xl border border-slate-200/90 bg-white shadow-2xl shadow-slate-900/10 overflow-hidden text-left">
<!-- Window Chrome (macOS Style) -->
<div class="flex items-center justify-between px-4 py-3 bg-[#F8FAFC] border-b border-slate-200/80">
<div class="flex items-center gap-2">
<span class="w-3 h-3 rounded-full bg-[#FF5F56] inline-block border border-black/10"></span>
<span class="w-3 h-3 rounded-full bg-[#FFBD2E] inline-block border border-black/10"></span>
<span class="w-3 h-3 rounded-full bg-[#27C93F] inline-block border border-black/10"></span>
</div>
<!-- URL Bar -->
<div class="flex items-center gap-2 bg-white px-5 py-1 rounded-md border border-slate-200 shadow-2xs text-[11px] text-slate-500 font-mono">
<span class="material-symbols-outlined text-[14px] text-blue-600">lock</span>
<span>app.meetflow.ai/workspace</span>
</div>
<div class="flex items-center gap-1.5 text-slate-400">
<span class="material-symbols-outlined text-[18px]">more_horiz</span>
</div>
</div>
<!-- Authentic MEEEASY-Style Application Layout -->
<div class="grid grid-cols-12 min-h-[540px] bg-[#F8FAFC]">
<!-- 1. Left Sidebar Rail (MEEEASY Sidebar) -->
<div class="hidden md:flex md:col-span-2 bg-white border-r border-slate-200 p-4 flex-col justify-between">
<div class="space-y-6">
<!-- Logo branding in app -->
<div class="flex items-center gap-2 px-1">
<div class="w-6 h-6 rounded-lg bg-blue-600 flex items-center justify-center text-white text-[13px] font-bold">
<span class="material-symbols-outlined text-[15px]">event_repeat</span>
</div>
<span class="text-sm font-bold text-slate-900 tracking-tight">MeetFlow</span>
</div>
<!-- Nav Section 1: Overview -->
<div class="space-y-1">
<span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2.5">Overview</span>
<a class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg bg-slate-900 text-white font-medium text-[12px] shadow-xs" href="#">
<span class="material-symbols-outlined text-[16px]">dashboard</span>
<span>Dashboard</span>
</a>
<a class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<div class="flex items-center gap-2.5">
<span class="material-symbols-outlined text-[16px] text-slate-400">video_camera_front</span>
<span>My Meetings</span>
</div>
<span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">3</span>
</a>
<a class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<span class="material-symbols-outlined text-[16px] text-slate-400">calendar_month</span>
<span>Calendar</span>
</a>
</div>
<!-- Nav Section 2: Intelligence -->
<div class="space-y-1">
<span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2.5">Intelligence</span>
<a class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<span class="material-symbols-outlined text-[16px] text-slate-400">description</span>
<span>Transcripts</span>
</a>
<a class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<span class="material-symbols-outlined text-[16px] text-purple-600">auto_awesome</span>
<span>AI Summaries</span>
</a>
<a class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<div class="flex items-center gap-2.5">
<span class="material-symbols-outlined text-[16px] text-slate-400">check_box</span>
<span>Action Items</span>
</div>
<span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded-full">5</span>
</a>
</div>
<!-- Nav Section 3: Workspace -->
<div class="space-y-1">
<span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-2.5">Workspace</span>
<a class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<span class="material-symbols-outlined text-[16px] text-slate-400">group</span>
<span>Team</span>
</a>
<a class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 font-medium text-[12px] transition-colors" href="#">
<span class="material-symbols-outlined text-[16px] text-slate-400">analytics</span>
<span>Analytics</span>
</a>
</div>
</div>
<!-- Mini Bottom Workspace Info -->
<div class="p-2.5 rounded-xl bg-purple-50 border border-purple-100 flex items-center gap-2">
<span class="material-symbols-outlined text-purple-600 text-[18px]">auto_awesome</span>
<div class="flex flex-col">
<span class="text-[10px] font-bold uppercase text-purple-700">Workspace AI</span>
<span class="text-[11px] font-medium text-slate-600">Auto-sync Active</span>
</div>
</div>
</div>
<!-- 2. Main Center & Right Dashboard Content -->
<div class="col-span-12 md:col-span-10 flex flex-col">
<!-- Top Mini App Header (Search + Actions) -->
<div class="h-14 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
<div class="flex items-center gap-3 w-full max-w-sm">
<div class="relative w-full">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">search</span>
<input class="w-full pl-8 pr-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-[12px] text-slate-600 placeholder-slate-400 focus:outline-none cursor-default" placeholder="Search meetings, transcripts, action items..." readonly="" type="text"/>
</div>
</div>
<div class="flex items-center gap-3">
<button class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600">
<span class="material-symbols-outlined text-[17px]">notifications</span>
</button>
<button class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600">
<span class="material-symbols-outlined text-[17px]">settings</span>
</button>
<div class="h-4 w-px bg-slate-200"></div>
<button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-white font-medium text-[12px] shadow-xs">
<span class="material-symbols-outlined text-[15px]">add</span>
<span>New Meeting</span>
</button>
</div>
</div>
<!-- Dashboard 2-Column Body (Recent Meetings / Live Transcript / AI Insights) -->
<div class="p-5 sm:p-6 grid grid-cols-12 gap-5">
<!-- Left Main Flow (8 cols) -->
<div class="col-span-12 lg:col-span-8 flex flex-col gap-4">
<!-- Section Title & Filter -->
<div class="flex items-center justify-between">
<h4 class="text-sm font-bold text-slate-900">Recent Meetings</h4>
<a class="text-blue-600 hover:underline text-[12px] font-semibold flex items-center gap-0.5" href="#">
<span>View All</span>
<span class="material-symbols-outlined text-[14px]">arrow_forward</span>
</a>
</div>
<!-- Highlighted In-Progress Meeting Card (MEEEASY Style) -->
<div class="p-4 rounded-xl bg-white border border-rose-200/80 shadow-xs flex items-center justify-between gap-4">
<div class="flex items-center gap-3.5">
<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-600 text-[11px] font-bold">
<span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
<span>LIVE</span>
</div>
<div>
<h5 class="text-[13px] font-bold text-slate-900">Website Project Kickoff</h5>
<div class="flex items-center gap-2 text-slate-500 text-[11px] mt-0.5">
<span>Started 15 min ago</span>
<span>•</span>
<span>8 participants</span>
<span>•</span>
<span class="text-rose-600 font-medium">Recording</span>
</div>
</div>
</div>
<div class="flex items-center gap-3">
<div class="hidden sm:flex -space-x-1.5">
<div class="w-6 h-6 rounded-full bg-slate-200 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-slate-700">AK</div>
<div class="w-6 h-6 rounded-full bg-blue-100 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-blue-700">TL</div>
<div class="w-6 h-6 rounded-full bg-purple-100 ring-2 ring-white flex items-center justify-center text-[9px] font-bold text-purple-700">SL</div>
<div class="w-6 h-6 rounded-full bg-slate-100 ring-2 ring-white flex items-center justify-center text-[9px] font-semibold text-slate-500">+5</div>
</div>
<button class="px-3.5 py-1.5 rounded-lg bg-rose-600 text-white font-semibold text-[12px] shadow-sm hover:bg-rose-700 transition-colors flex items-center gap-1">
<span class="material-symbols-outlined text-[15px]">videocam</span>
<span>Join Now</span>
</button>
</div>
</div>
<!-- AI Meeting Summary Widget -->
<div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex flex-col gap-2">
<div class="flex items-center justify-between">
<div class="flex items-center gap-1.5">
<div class="w-5 h-5 rounded bg-purple-100 text-purple-700 flex items-center justify-center">
<span class="material-symbols-outlined text-[13px]">auto_awesome</span>
</div>
<span class="text-[12px] font-bold text-slate-900">AI Meeting Summary</span>
</div>
<span class="text-[9px] font-bold uppercase tracking-wider text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">GENERATED JUST NOW</span>
</div>
<p class="text-[12px] text-slate-600 leading-relaxed">
                        The team agreed to prioritize <span class="bg-blue-50 text-blue-700 font-semibold px-1 rounded border border-blue-200">authentication</span> and <span class="bg-blue-50 text-blue-700 font-semibold px-1 rounded border border-blue-200">meeting scheduling</span> in Sprint 1 before addressing analytics export modules.
                      </p>
</div>
<!-- Extracted Action Items Checklist -->
<div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs flex flex-col gap-2.5">
<div class="flex items-center justify-between">
<span class="text-[12px] font-bold text-slate-900">Extracted Action Items</span>
<span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">3 Tasks Ready</span>
</div>
<div class="space-y-1.5">
<div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/80 border border-slate-200/60">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[16px] text-blue-600">check_circle</span>
<span class="text-[11px] font-medium text-slate-800">Complete database schema</span>
</div>
<span class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600">Due Fri</span>
</div>
<div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/80 border border-slate-200/60">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[16px] text-slate-400">radio_button_unchecked</span>
<span class="text-[11px] text-slate-700">Review authentication flow</span>
</div>
<span class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600">Due Mon</span>
</div>
<div class="flex items-center justify-between p-2 rounded-lg bg-slate-50/80 border border-slate-200/60">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[16px] text-slate-400">radio_button_unchecked</span>
<span class="text-[11px] text-slate-700">Prepare sprint agenda</span>
</div>
<span class="text-[9px] font-semibold px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600">Due Tue</span>
</div>
</div>
</div>
</div>
<!-- Right Rail Widget: Upcoming Today (MEEEASY Right Column) -->
<div class="col-span-12 lg:col-span-4 flex flex-col gap-4">
<div class="flex items-center justify-between">
<h4 class="text-sm font-bold text-slate-900">Upcoming Today</h4>
<a class="text-blue-600 hover:underline text-[12px] font-semibold" href="#">Calendar →</a>
</div>
<div class="space-y-2.5">
<!-- Upcoming Item 1 -->
<div class="p-3 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
<div class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-center shrink-0">
<span class="block text-[11px] font-bold text-slate-900 leading-tight">11:00</span>
<span class="block text-[9px] uppercase font-bold text-slate-400">AM</span>
</div>
<div class="flex-1 min-w-0">
<span class="block text-[12px] font-bold text-slate-900 truncate">Design Review Session</span>
<span class="block text-[10px] text-slate-500">Product &amp; UX Team</span>
</div>
</div>
<!-- Upcoming Item 2 -->
<div class="p-3 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
<div class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-center shrink-0">
<span class="block text-[11px] font-bold text-slate-900 leading-tight">02:00</span>
<span class="block text-[9px] uppercase font-bold text-slate-400">PM</span>
</div>
<div class="flex-1 min-w-0">
<span class="block text-[12px] font-bold text-slate-900 truncate">Investor Update Call</span>
<span class="block text-[10px] text-slate-500">Board Members</span>
</div>
</div>
<!-- Upcoming Item 3 -->
<div class="p-3 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center gap-3">
<div class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-center shrink-0">
<span class="block text-[11px] font-bold text-slate-900 leading-tight">04:30</span>
<span class="block text-[9px] uppercase font-bold text-slate-400">PM</span>
</div>
<div class="flex-1 min-w-0">
<span class="block text-[12px] font-bold text-slate-900 truncate">1:1 with Engineering Lead</span>
<span class="block text-[10px] text-slate-500">Sprint Sync</span>
</div>
</div>
</div>
<!-- AI Insights Mini Card -->
<div class="p-3.5 rounded-xl bg-violet-50/70 border border-violet-200/80 flex flex-col gap-2 mt-1">
<div class="flex items-center gap-1.5 text-violet-800">
<span class="material-symbols-outlined text-[16px]">lightbulb</span>
<span class="text-[11px] font-bold uppercase tracking-wider">AI Insights</span>
</div>
<p class="text-[11px] text-slate-700 leading-normal">
                        Your meetings were <strong class="text-violet-900">23% more productive</strong> this week due to automated task distribution.
                      </p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- 3. FEATURES SECTION ("Everything Your Meetings Need" - TaskGo 4-Card Rhythm) -->
<section class="w-full py-24 bg-white border-y border-slate-200/80" id="features">
<div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col gap-14">
<!-- Section Header -->
<div class="flex flex-col items-center text-center max-w-2xl mx-auto gap-3">
<span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">Features</span>
<h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">
            Everything Your Meetings Need
          </h2>
<p class="text-base lg:text-lg text-slate-600">
            From planning to follow-up, MeetFlow keeps your team’s meeting workflow organized.
          </p>
</div>
<!-- 4-Column Feature Grid (TaskGo Style Card Aesthetics) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
<!-- Feature 1 -->
<div class="group p-7 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-blue-200 transition-all duration-300 flex flex-col">
<div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-6 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined text-[24px]">calendar_today</span>
</div>
<h3 class="text-lg font-bold text-slate-900 mb-2">Meeting Management</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-6">
              Create, schedule, and organize meetings in one centralized workspace.
            </p>
<div class="mt-auto pt-2 flex items-center gap-1 text-blue-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
<span>Learn more</span>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
</div>
</div>
<!-- Feature 2 -->
<div class="group p-7 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-blue-200 transition-all duration-300 flex flex-col">
<div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 mb-6 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined text-[24px]">description</span>
</div>
<h3 class="text-lg font-bold text-slate-900 mb-2">Structured Meeting Notes</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-6">
              Keep agendas, discussions, and important decisions organized.
            </p>
<div class="mt-auto pt-2 flex items-center gap-1 text-blue-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
<span>Learn more</span>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
</div>
</div>
<!-- Feature 3 -->
<div class="group p-7 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-purple-200 transition-all duration-300 flex flex-col">
<div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 mb-6 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined text-[24px]">auto_awesome</span>
</div>
<h3 class="text-lg font-bold text-slate-900 mb-2">AI Meeting Summaries</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-6">
              Turn written meeting notes into concise and structured summaries.
            </p>
<div class="mt-auto pt-2 flex items-center gap-1 text-blue-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
<span>Learn more</span>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
</div>
</div>
<!-- Feature 4 -->
<div class="group p-7 rounded-2xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-blue-200 transition-all duration-300 flex flex-col">
<div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-6 group-hover:scale-105 transition-transform">
<span class="material-symbols-outlined text-[24px]">checklist</span>
</div>
<h3 class="text-lg font-bold text-slate-900 mb-2">Action Items</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-6">
              Identify follow-up tasks and track responsibilities after every meeting.
            </p>
<div class="mt-auto pt-2 flex items-center gap-1 text-blue-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
<span>Learn more</span>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
</div>
</div>
</div>
</div>
</section>
<!-- 4. HOW IT WORKS SECTION (TaskGo Side-by-Side: UI Card + 3 Steps) -->
<section class="w-full py-24 bg-[#F8FAFC]" id="how-it-works">
<div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col gap-14">
<!-- Section Header -->
<div class="flex flex-col items-center text-center max-w-2xl mx-auto gap-3">
<span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">Workflow</span>
<h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">
            From Meeting to Meaningful Progress
          </h2>
<p class="text-base lg:text-lg text-slate-600">
            A simple workflow that helps your team stay organized before, during, and after every meeting.
          </p>
</div>
<!-- Balanced TaskGo Layout: Left Product UI Preview + Right 3 Numbered Steps -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
<!-- Left: Compact Product UI Workflow Card (TaskGo left container) -->
<div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col gap-4">
<!-- App Bar Mock -->
<div class="flex items-center justify-between pb-3 border-b border-slate-100">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
<span class="text-xs font-bold text-slate-900">Sprint Execution Hub</span>
</div>
<span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Live Sync Active</span>
</div>
<!-- Workflow Mockup Internal Elements -->
<div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col gap-2">
<div class="flex items-center justify-between">
<span class="text-[12px] font-bold text-slate-800">Meeting Agenda &amp; Scope</span>
<span class="text-[10px] text-slate-500 font-mono">10:00 - 10:45 AM</span>
</div>
<div class="space-y-1.5 text-[12px] text-slate-600">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[15px] text-blue-600">check_circle</span>
<span>Review sprint goals and auth architecture</span>
</div>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[15px] text-blue-600">check_circle</span>
<span>Confirm database migration timeline</span>
</div>
</div>
</div>
<!-- AI Output Snapshot inside card -->
<div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-100 flex flex-col gap-1.5">
<div class="flex items-center gap-1.5 text-purple-700">
<span class="material-symbols-outlined text-[16px]">auto_awesome</span>
<span class="text-[11px] font-bold uppercase tracking-wider">Automated Note Syntheses</span>
</div>
<p class="text-[12px] text-slate-700 leading-relaxed">
                Summary generated in 1.4s with 3 key decisions and 4 tagged deliverables.
              </p>
</div>
<div class="flex items-center justify-between pt-1 text-slate-500 text-[12px]">
<span class="flex items-center gap-1">
<span class="material-symbols-outlined text-[16px] text-blue-600">cloud_done</span>
<span>All changes saved to workspace</span>
</span>
<span class="font-semibold text-slate-700">4 Attendees synced</span>
</div>
</div>
<!-- Right: Three Structured Numbered Steps -->
<div class="lg:col-span-6 flex flex-col gap-4">
<!-- Step 1 -->
<div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col gap-2 hover:border-blue-200 transition-colors">
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                    01
                  </div>
<h3 class="text-base font-bold text-slate-900">Create a Meeting</h3>
</div>
<span class="text-[10px] font-bold uppercase text-blue-700 bg-blue-50 border border-blue-200/60 px-2.5 py-1 rounded-full">PREPARATION</span>
</div>
<p class="text-sm text-slate-600 leading-relaxed pl-13">
                Set the meeting title, schedule, participants, and agenda.
              </p>
<div class="mt-2 pl-13 flex items-center gap-2 text-slate-500 text-[12px]">
<span class="material-symbols-outlined text-[16px] text-blue-600">event</span>
<span>Synced to Google &amp; Outlook</span>
</div>
</div>
<!-- Step 2 -->
<div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col gap-2 hover:border-blue-200 transition-colors">
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                    02
                  </div>
<h3 class="text-base font-bold text-slate-900">Document the Discussion</h3>
</div>
<span class="text-[10px] font-bold uppercase text-slate-700 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">EXECUTION</span>
</div>
<p class="text-sm text-slate-600 leading-relaxed pl-13">
                Record meeting notes, key discussion points, and important decisions.
              </p>
<div class="mt-2 pl-13 flex items-center gap-2 text-slate-500 text-[12px]">
<span class="material-symbols-outlined text-[16px] text-blue-600">edit</span>
<span>Collaborative real-time editor</span>
</div>
</div>
<!-- Step 3 -->
<div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-2xs flex flex-col gap-2 hover:border-purple-200 transition-colors">
<div class="flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                    03
                  </div>
<h3 class="text-base font-bold text-slate-900">Let AI Assist</h3>
</div>
<span class="text-[10px] font-bold uppercase text-purple-700 bg-purple-50 border border-purple-200/60 px-2.5 py-1 rounded-full">AUTOMATION</span>
</div>
<p class="text-sm text-slate-600 leading-relaxed pl-13">
                Generate summaries and suggested action items from your meeting notes.
              </p>
<div class="mt-2 pl-13 flex items-center gap-2 text-slate-500 text-[12px]">
<span class="material-symbols-outlined text-[16px] text-purple-600">auto_awesome</span>
<span>Zero manual note syntheses</span>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- 5. AI SHOWCASE SECTION ("MEETFLOW AI" / "Less Time Writing. More Time Doing.") -->
<section class="w-full py-24 bg-white border-y border-slate-200/80" id="ai-assistant">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="relative rounded-3xl p-8 lg:p-14 bg-gradient-to-br from-violet-50/70 via-white to-blue-50/50 border border-violet-100 shadow-xl shadow-slate-900/5 overflow-hidden">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
<!-- Left Info -->
<div class="lg:col-span-6 flex flex-col items-start">
<div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 border border-purple-200 text-purple-700 shadow-xs mb-5">
<span class="material-symbols-outlined text-[16px]">bolt</span>
<span class="text-[11px] font-bold tracking-wider uppercase">MEETFLOW AI</span>
</div>
<h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                Less Time Writing.<br/>More Time Doing.
              </h2>
<p class="text-base lg:text-lg text-slate-600 leading-relaxed mb-8">
                MeetFlow AI helps transform meeting notes into useful summaries, decisions, and follow-up tasks.
              </p>
<!-- Feature Bullets -->
<div class="space-y-4 w-full">
<div class="flex items-start gap-3.5">
<div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 mt-0.5">
<span class="material-symbols-outlined text-[18px]">format_list_bulleted</span>
</div>
<div>
<h4 class="text-[15px] font-bold text-slate-900">Generate Meeting Summaries</h4>
<p class="text-sm text-slate-600">Distills complex discussions into clear bullet points.</p>
</div>
</div>
<div class="flex items-start gap-3.5">
<div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
<span class="material-symbols-outlined text-[18px]">psychology</span>
</div>
<div>
<h4 class="text-[15px] font-bold text-slate-900">Extract Key Decisions</h4>
<p class="text-sm text-slate-600">Isolates critical project agreements with consensus tags.</p>
</div>
</div>
<div class="flex items-start gap-3.5">
<div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
<span class="material-symbols-outlined text-[18px]">add_task</span>
</div>
<div>
<h4 class="text-[15px] font-bold text-slate-900">Suggest Action Items</h4>
<p class="text-sm text-slate-600">Automatically drafts assigned tasks and target deadlines.</p>
</div>
</div>
</div>
</div>
<!-- Right Interactive Preview Card -->
<div class="lg:col-span-6 flex flex-col">
<div class="p-6 sm:p-7 rounded-2xl bg-white border border-slate-200 shadow-xl shadow-purple-900/5 flex flex-col gap-4">
<!-- Assistant Card Header -->
<div class="flex items-center justify-between pb-3 border-b border-slate-100">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shadow-xs">
<span class="material-symbols-outlined text-[22px]">smart_toy</span>
</div>
<div>
<h3 class="text-base font-bold text-slate-900">AI Meeting Assistant</h3>
<div class="flex items-center gap-2">
<span class="text-[11px] font-bold text-blue-600">Sub-2s Response</span>
<span class="text-[11px] text-slate-400">• Illustrative AI Output</span>
</div>
</div>
</div>
<span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-wider">ACTIVE AGENT</span>
</div>
<!-- Meeting Summary Box -->
<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 flex flex-col gap-2">
<span class="text-[12px] font-bold text-slate-900 flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px] text-blue-600">notes</span>
                    Meeting Summary
                  </span>
<ul class="space-y-1.5 text-[12px] text-slate-600 leading-relaxed">
<li class="flex items-start gap-2">
<span class="text-blue-600 font-bold">•</span>
<span>Authentication is the first development priority.</span>
</li>
<li class="flex items-start gap-2">
<span class="text-blue-600 font-bold">•</span>
<span>Database design will be completed this week.</span>
</li>
<li class="flex items-start gap-2">
<span class="text-blue-600 font-bold">•</span>
<span>The team agreed to review progress next Monday.</span>
</li>
</ul>
</div>
<!-- Key Decision Box -->
<div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200/60 flex flex-col gap-1.5">
<div class="flex items-center justify-between">
<span class="text-[12px] font-bold text-blue-900 flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px] text-blue-600">gavel</span>
                      Key Decision
                    </span>
<span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded bg-blue-100 text-blue-700">APPROVED</span>
</div>
<p class="text-[12px] font-medium text-slate-800">
                    "Prioritize authentication before implementing additional features."
                  </p>
</div>
<!-- Suggested Action Item Card -->
<div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs flex flex-col gap-2.5">
<div class="flex items-center justify-between">
<span class="text-[12px] font-bold text-slate-900 flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px] text-blue-600">task</span>
                      Suggested Action Item
                    </span>
<span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded bg-purple-100 text-purple-700">CONFIDENCE 98%</span>
</div>
<div class="grid grid-cols-3 gap-2 p-2.5 rounded-lg bg-slate-50 border border-slate-200/60 text-[11px]">
<div>
<span class="text-slate-400 block text-[9px] font-bold uppercase">Task</span>
<span class="font-semibold text-slate-900">Complete schema</span>
</div>
<div>
<span class="text-slate-400 block text-[9px] font-bold uppercase">Assignee</span>
<span class="font-semibold text-slate-900">Project Team</span>
</div>
<div>
<span class="text-slate-400 block text-[9px] font-bold uppercase">Due Date</span>
<span class="font-semibold text-slate-900">Next Monday</span>
</div>
</div>
</div>
<!-- Review and Approval Bar -->
<div class="flex items-center justify-between pt-1">
<div class="flex items-center gap-1.5 text-slate-500">
<span class="material-symbols-outlined text-[16px]">hourglass_top</span>
<span class="text-[11px]">Pending user review and approval</span>
</div>
<div class="flex items-center gap-2">
<button class="px-3 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold hover:bg-slate-200 transition-colors" type="button">Edit</button>
<button class="px-3 py-1 rounded-md bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700 transition-colors shadow-2xs" type="button">Approve All</button>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- 6. BENEFITS SECTION ("Meetings That Move Your Team Forward") -->
<section class="w-full py-24 bg-[#F8FAFC]">
<div class="max-w-7xl mx-auto px-6 lg:px-12 flex flex-col gap-14">
<!-- Section Header -->
<div class="flex flex-col items-center text-center max-w-2xl mx-auto gap-3">
<span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-200/60">Benefits</span>
<h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">
            Meetings That Move Your Team Forward
          </h2>
<p class="text-base lg:text-lg text-slate-600">
            Built for clarity, speed, and real execution across modern distributed organizations.
          </p>
</div>
<!-- 3 Benefit Cards (TaskGo Rhythm) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- Benefit 1 -->
<div class="flex flex-col p-8 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-[24px]">folder_special</span>
</div>
<h3 class="text-xl font-bold text-slate-900 mb-3">Stay Organized</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-8">
              Keep meeting information, agendas, and notes in one place. No more lost links or fragmented Slack threads.
            </p>
<div class="mt-auto pt-4 border-t border-slate-100">
<div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
<span class="material-symbols-outlined text-blue-600 text-[20px]">inventory_2</span>
<span class="text-[12px] font-semibold text-slate-800">One repository for all meeting data</span>
</div>
</div>
</div>
<!-- Benefit 2 -->
<div class="flex flex-col p-8 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-[24px]">diversity_3</span>
</div>
<h3 class="text-xl font-bold text-slate-900 mb-3">Improve Team Clarity</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-8">
              Make decisions and responsibilities easier to understand. Ensure every teammate leaves knowing who owns what.
            </p>
<div class="mt-auto pt-4 border-t border-slate-100">
<div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
<span class="material-symbols-outlined text-blue-600 text-[20px]">assignment_turned_in</span>
<span class="text-[12px] font-semibold text-slate-800">Clear stakeholder ownership</span>
</div>
</div>
</div>
<!-- Benefit 3 -->
<div class="flex flex-col p-8 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
<div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-[24px]">timer_off</span>
</div>
<h3 class="text-xl font-bold text-slate-900 mb-3">Save Documentation Time</h3>
<p class="text-sm text-slate-600 leading-relaxed mb-8">
              Use AI assistance to reduce repetitive note-taking work. Free up hours spent summarizing and drafting recaps.
            </p>
<div class="mt-auto pt-4 border-t border-slate-100">
<div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-3">
<span class="material-symbols-outlined text-blue-600 text-[20px]">trending_up</span>
<span class="text-[12px] font-semibold text-slate-800">Saves 4+ hours per team weekly</span>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- 7. FINAL CTA SECTION (Deep Navy High-Contrast Banner) -->
<section class="w-full py-20 bg-white">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<div class="relative overflow-hidden rounded-3xl bg-[#111827] px-8 py-16 lg:py-20 text-center shadow-2xl">
<!-- Subtle radial lighting accents -->
<div class="absolute -top-32 -left-32 w-80 h-80 rounded-full bg-blue-600/30 blur-3xl pointer-events-none"></div>
<div class="absolute -bottom-32 -right-32 w-80 h-80 rounded-full bg-purple-600/20 blur-3xl pointer-events-none"></div>
<div class="relative z-10 max-w-2xl mx-auto flex flex-col items-center">
<!-- Pill -->
<div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-slate-200 text-[11px] font-bold uppercase tracking-wider mb-6">
<span class="material-symbols-outlined text-[16px] text-blue-400">rocket_launch</span>
<span>GET STARTED IN UNDER 2 MINUTES</span>
</div>
<!-- Headline -->
<h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight mb-4">
              Turn Your Next Meeting Into Meaningful Progress.
            </h2>
<!-- Subtext -->
<p class="text-base sm:text-lg text-slate-300 leading-relaxed mb-8">
              Start organizing your meetings, notes, and follow-up tasks with MeetFlow.
            </p>
<!-- Buttons -->
<div class="flex flex-wrap items-center justify-center gap-3.5 w-full sm:w-auto mb-6">
<a class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[15px] shadow-lg shadow-blue-600/30 transition-all" data-path="get-started" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">
<span>Get Started</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</a>
<a class="inline-flex items-center justify-center px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white border border-white/20 font-semibold text-[15px] transition-colors" data-path="sign-in" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">
<span>Sign In</span>
</a>
</div>
<!-- Footer badge reassurance -->
<p class="text-[13px] text-slate-400">
              Free 14-day trial • No credit card required • Enterprise SOC2 compliant
            </p>
</div>
</div>
</div>
</section>
</main>
<!-- 8. FOOTER -->
<footer class="w-full bg-white border-t border-slate-200">
<div class="max-w-7xl mx-auto px-6 lg:px-12 py-16">
<div class="grid grid-cols-1 md:grid-cols-5 gap-10">
<!-- Brand column -->
<div class="md:col-span-2 flex flex-col gap-3">
<div class="flex items-center gap-2.5">
<div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-xs">
<span class="material-symbols-outlined text-[18px]">event_repeat</span>
</div>
<span class="text-xl font-bold text-slate-900 tracking-tight">MeetFlow</span>
</div>
<p class="text-sm font-medium text-slate-700 mt-1">Plan Better. Meet Smarter. Follow Through.</p>
<p class="text-xs text-slate-500 max-w-sm">Autonomous meeting intelligence for high-output teams.</p>
</div>
<!-- Links column 1 -->
<div class="flex flex-col gap-3">
<span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Product</span>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="features" href="#features">Features</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="how-it-works" href="#how-it-works">How It Works</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="ai-assistant" href="#ai-assistant">AI Assistant</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="pricing" href="#">Pricing</a>
</div>
<!-- Links column 2 -->
<div class="flex flex-col gap-3">
<span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Resources</span>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="documentation" href="#">Documentation</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="changelog" href="#">Changelog</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="security" href="#">Security &amp; Trust</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="integrations" href="#">Integrations</a>
</div>
<!-- Links column 3 -->
<div class="flex flex-col gap-3">
<span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Company</span>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="about" href="#">About Us</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="privacy-policy" href="#">Privacy Policy</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="terms-of-service" href="#">Terms of Service</a>
<a class="text-sm text-slate-600 hover:text-slate-900 transition-colors" data-path="contact" href="#">Contact Support</a>
</div>
</div>
<!-- Bottom Bar -->
<div class="mt-12 pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-xs text-slate-500">© 2026 MeetFlow Technologies Inc. All rights reserved.</p>
<div class="flex items-center gap-2 text-xs font-semibold text-emerald-600">
<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
<span>All Systems Operational</span>
</div>
</div>
</div>
</footer>
</body></html>