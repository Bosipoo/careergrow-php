<?php
// Seed demo data if DB is empty (optional)
require 'db.php';
$db = getDB();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Tracker MVP</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        /* Chart Container Strict Styling */
        .chart-container {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            height: 300px;
            max-height: 400px;
        }
        @media (min-width: 768px) {
            .chart-container { height: 350px; }
        }

        /* Status Pill Colors - Refined Palette */
        .status-applied { background-color: #f1f5f9; color: #475569; } /* Slate */
        .status-screening { background-color: #ecfeff; color: #0891b2; } /* Cyan */
        .status-interview { background-color: #fff7ed; color: #ea580c; } /* Orange */
        .status-offer { background-color: #f0fdf4; color: #16a34a; } /* Emerald */
        .status-rejected { background-color: #fef2f2; color: #dc2626; } /* Red */

        /* Custom Dropdown Styling for table */
        .status-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.25rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 1.5rem;
        }

        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Custom scrollbar for better aesthetics */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <!-- Theme Transition: 
         Primary: Emerald-600 (Fresh, professional, distinct from Blue/Purple)
         Secondary: Slate-600
         Accent: Orange-500 (for warnings/interviews)
    -->
</head>
<body class="text-slate-800 antialiased">

    <!-- Navigation -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <i class="fa-solid fa-seedling text-emerald-600 text-2xl mr-2"></i>
                        <span class="font-bold text-xl text-slate-900 tracking-tight">CareerGrow</span>
                    </div>
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                        <button onclick="router.navigate('dashboard')" id="nav-dashboard" class="nav-link border-emerald-500 text-slate-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </button>
                        <button onclick="router.navigate('analytics')" id="nav-analytics" class="nav-link border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Analytics
                        </button>
                    </div>
                </div>
                <div class="flex items-center">
                    <button onclick="router.navigate('create')" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition shadow-md">
                        <i class="fa-solid fa-plus mr-2"></i> New Application
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="app-content" class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 fade-in"></main>

    <!-- Delete Modal -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-slate-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative p-5 border w-96 shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-50">
                    <i class="fa-solid fa-trash-can text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mt-4">Remove Application?</h3>
                <p class="text-sm text-slate-500 mt-2 px-7">Are you sure you want to delete this? This data cannot be recovered.</p>
                <div class="items-center px-4 py-3 space-y-2 mt-4">
                    <button id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white font-medium rounded-md w-full shadow-sm hover:bg-red-700 transition">Delete Permanentely</button>
                    <button onclick="ui.closeModal()" class="px-4 py-2 bg-white text-slate-700 font-medium rounded-md w-full border border-slate-200 hover:bg-slate-50 transition">Keep It</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed bottom-5 right-5 bg-slate-800 text-white px-6 py-3 rounded-lg shadow-2xl z-50 transform transition-all duration-300 flex items-center">
        <i class="fa-solid fa-circle-check text-emerald-400 mr-3"></i>
        <span id="toast-message"></span>
    </div>

    <!-- Templates -->
    <template id="view-dashboard">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="summary-ribbon"></div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0 md:space-x-4">
                <div class="relative w-full md:w-1/3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                    </div>
                    <input type="text" id="search-input" placeholder="Search company or role..." class="pl-10 block w-full rounded-lg border-slate-200 bg-slate-50 border focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm py-2.5 px-3 transition-all" oninput="app.handleFilter()">
                </div>
                
                <div class="w-full md:w-1/4">
                    <select id="status-filter" class="block w-full rounded-lg border-slate-200 bg-slate-50 border focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm py-2.5 px-3 transition-all" onchange="app.handleFilter()">
                        <option value="All">All Statuses</option>
                        <option value="Applied">Applied</option>
                        <option value="Screening">Screening</option>
                        <option value="Interview">Interview</option>
                        <option value="Offer">Offer</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                <div class="hidden md:block text-sm text-slate-500 text-right w-full"><span id="record-count" class="font-bold text-slate-700">0</span> applications tracking</div>
            </div>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Follow-up</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200" id="applications-table-body"></tbody>
                    </table>
                </div>
                <div id="empty-state" class="hidden text-center py-20 bg-slate-50/50">
                    <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="fa-solid fa-leaf text-3xl text-slate-200"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">No applications found</h3>
                    <p class="text-slate-500 mt-1">Start growing your career by adding a new application.</p>
                    <div class="mt-8">
                        <button onclick="router.navigate('create')" class="inline-flex items-center px-6 py-3 text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-md">
                            <i class="fa-solid fa-plus mr-2"></i> Add First Application
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="view-form">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center mb-6">
                <button onclick="router.navigate('dashboard')" class="mr-4 text-slate-400 hover:text-emerald-600 transition">
                    <i class="fa-solid fa-arrow-left text-xl"></i>
                </button>
                <h2 id="form-title" class="text-2xl font-bold text-slate-900">Add New Application</h2>
            </div>
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-slate-100">
                <form id="app-form" onsubmit="app.handleSave(event)" class="p-8 space-y-6">
                    <input type="hidden" name="id" id="field-id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Company Name</label>
                            <input type="text" name="company" id="field-company" required placeholder="e.g. Google" class="block w-full rounded-lg border-slate-200 border p-3 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-all outline-none">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Job Title</label>
                            <input type="text" name="role" id="field-role" required placeholder="e.g. Senior Designer" class="block w-full rounded-lg border-slate-200 border p-3 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-all outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Current Stage</label>
                            <select name="status" id="field-status" class="block w-full rounded-lg border-slate-200 border p-3 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-all outline-none bg-white">
                                <option>Applied</option><option>Screening</option><option>Interview</option><option>Offer</option><option>Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Next Action Date</label>
                            <input type="date" name="next_action" id="field-next_action" required class="block w-full rounded-lg border-slate-200 border p-3 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-all outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Notes / Reminders</label>
                        <textarea name="notes" id="field-notes" rows="4" placeholder="Salary range, tech stack, or interviewer names..." class="block w-full rounded-lg border-slate-200 border p-3 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm transition-all outline-none"></textarea>
                    </div>
                    <div class="flex items-center justify-end pt-6 border-t border-slate-100">
                        <button type="button" onclick="router.navigate('dashboard')" class="mr-4 text-sm text-slate-500 font-semibold hover:text-slate-700 transition">Cancel</button>
                        <button type="submit" id="form-submit-btn" class="px-8 py-3 bg-emerald-600 text-white text-sm font-bold uppercase tracking-wider rounded-lg hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">Save Application</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <template id="view-analytics">
        <div class="space-y-6">
            <h2 class="text-2xl font-bold text-slate-900">Analytics Overview</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                        <i class="fa-solid fa-chart-pie text-emerald-500 mr-2"></i> Status Distribution
                    </h3>
                    <div class="chart-container"><canvas id="statusChart"></canvas></div>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center">
                        <i class="fa-solid fa-chart-line text-emerald-500 mr-2"></i> Application Volume
                    </h3>
                    <div class="chart-container"><canvas id="timelineChart"></canvas></div>
                </div>
            </div>
        </div>
    </template>

    <script>
        const state = {
            currentView: 'dashboard',
            applications: [],
            filter: { status: 'All', search: '' },
            editingId: null
        };

        // API Layer - All CRUD operations
        const api = {
            getAll: async () => {
                const res = await fetch('/api.php');
                state.applications = await res.json();
                return state.applications;
            },

            create: async (data) => {
                const res = await fetch('/api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                return await res.json();
            },

            update: async (id, data) => {
                const res = await fetch(`/api.php?id=${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                return await res.json();
            },

            delete: async (id) => {
                const res = await fetch(`/api.php?id=${id}`, {
                    method: 'DELETE'
                });
                return await res.json();
            }
        };

        const router = {
            navigate: (viewName, id = null) => {
                state.currentView = viewName;
                state.editingId = id;
                
                const templateId = (viewName === 'create' || viewName === 'edit') ? 'view-form' : `view-${viewName}`;
                const template = document.getElementById(templateId);
                const appContent = document.getElementById('app-content');
                
                appContent.innerHTML = '';
                appContent.appendChild(template.content.cloneNode(true));
                
                // Update Nav States
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('border-emerald-500', 'text-slate-900');
                    link.classList.add('border-transparent', 'text-slate-500');
                });
                const activeNav = document.getElementById(`nav-${viewName}`);
                if (activeNav) {
                    activeNav.classList.remove('border-transparent', 'text-slate-500');
                    activeNav.classList.add('border-emerald-500', 'text-slate-900');
                }

                if (viewName === 'dashboard') ui.renderDashboard();
                if (viewName === 'analytics') ui.renderAnalytics();
                if (viewName === 'create' || viewName === 'edit') ui.initForm(viewName, id);
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };

        const ui = {
            renderDashboard: () => {
                const tbody = document.getElementById('applications-table-body');
                const ribbon = document.getElementById('summary-ribbon');
                const filteredApps = app.getFilteredApplications();
                const today = new Date().toISOString().split('T')[0];

                const stats = app.calculateStats();
                ribbon.innerHTML = `
                    ${ui.createCard('Offers Received', stats.offers, 'fa-trophy', 'text-emerald-600', 'bg-emerald-50')}
                    ${ui.createCard('Active Pipeline', stats.active, 'fa-bolt', 'text-cyan-600', 'bg-cyan-50')}
                    ${ui.createCard('Attention Required', stats.pendingActions, 'fa-fire', 'text-orange-600', 'bg-orange-50')}
                    ${ui.createCard('Success Rate', stats.successRate + '%', 'fa-chart-simple', 'text-slate-600', 'bg-slate-50')}
                `;

                tbody.innerHTML = '';
                if (filteredApps.length === 0) {
                    document.getElementById('empty-state').classList.remove('hidden');
                } else {
                    document.getElementById('empty-state').classList.add('hidden');
                    filteredApps.forEach(job => {
                        const isOverdue = job.next_action < today && !['Offer', 'Rejected'].includes(job.status);
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-50 transition-colors group';
                        tr.innerHTML = `
                            <td class="px-6 py-5 whitespace-nowrap font-bold text-slate-900">${job.company}</td>
                            <td class="px-6 py-5 whitespace-nowrap text-slate-600 text-sm">${job.role}</td>
                            <td class="px-6 py-5 whitespace-nowrap">
                                <select onchange="app.updateStatus(${job.id}, this.value)" 
                                        class="status-select text-xs font-bold rounded-full px-3 py-1.5 status-${job.status.toLowerCase()} border-none focus:ring-0 cursor-pointer shadow-sm">
                                    ${['Applied', 'Screening', 'Interview', 'Offer', 'Rejected'].map(s => 
                                        `<option value="${s}" ${job.status === s ? 'selected' : ''}>${s}</option>`
                                    ).join('')}
                                </select>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm ${isOverdue ? 'text-orange-600 font-bold' : 'text-slate-500'}">
                                ${isOverdue ? '<i class="fa-solid fa-triangle-exclamation mr-1"></i>' : '<i class="fa-regular fa-calendar-check mr-2 opacity-50"></i>'}${job.next_action}
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-right space-x-2">
                                <button onclick="router.navigate('edit', ${job.id})" class="p-2 text-slate-300 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button onclick="app.deleteConfirm(${job.id})" class="p-2 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"><i class="fa-solid fa-trash-can"></i></button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
                document.getElementById('record-count').innerText = filteredApps.length;
            },

            createCard: (title, value, icon, colorClass, bgClass) => `
                <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-4 rounded-xl ${bgClass}"><i class="fa-solid ${icon} ${colorClass} text-xl"></i></div>
                        <div class="ml-5"><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">${title}</dt><dd class="text-2xl font-black text-slate-900">${value}</dd></div>
                    </div>
                </div>`,

            initForm: (type, id) => {
                const title = document.getElementById('form-title');
                const btn = document.getElementById('form-submit-btn');
                
                if (type === 'edit') {
                    const job = state.applications.find(a => a.id === id);
                    title.innerText = `Edit: ${job.company}`;
                    btn.innerText = 'Save Changes';
                    document.getElementById('field-id').value = job.id;
                    document.getElementById('field-company').value = job.company;
                    document.getElementById('field-role').value = job.role;
                    document.getElementById('field-status').value = job.status;
                    document.getElementById('field-next_action').value = job.next_action;
                    document.getElementById('field-notes').value = job.notes;
                } else {
                    const tomorrow = new Date(); tomorrow.setDate(tomorrow.getDate() + 1);
                    document.getElementById('field-next_action').value = tomorrow.toISOString().split('T')[0];
                }
            },

            showToast: (msg) => {
                const toast = document.getElementById('toast');
                document.getElementById('toast-message').innerText = msg;
                toast.classList.remove('hidden', 'opacity-0', 'translate-y-5');
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-5');
                    setTimeout(() => toast.classList.add('hidden'), 300);
                }, 3000);
            },

            renderAnalytics: () => {
                const ctxStatus = document.getElementById('statusChart').getContext('2d');
                const ctxTime = document.getElementById('timelineChart').getContext('2d');
                
                const statusCounts = { Applied: 0, Screening: 0, Interview: 0, Offer: 0, Rejected: 0 };
                state.applications.forEach(a => statusCounts[a.status]++);
                
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusCounts),
                        datasets: [{
                            data: Object.values(statusCounts),
                            backgroundColor: ['#94a3b8', '#0891b2', '#ea580c', '#16a34a', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
                        cutout: '70%'
                    }
                });

                new Chart(ctxTime, {
                    type: 'bar',
                    data: {
                        labels: ['Oct', 'Nov', 'Dec', 'Jan'],
                        datasets: [{ 
                            label: 'Applications Created', 
                            data: [1, 0, 3, 0], 
                            backgroundColor: '#059669',
                            borderRadius: 8
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } },
                        plugins: { legend: { display: false } }
                    }
                });
            },

            closeModal: () => document.getElementById('delete-modal').classList.add('hidden')
        };

        const app = {
            calculateStats: () => {
                const today = new Date().toISOString().split('T')[0];
                const total = state.applications.length;
                return {
                    offers: state.applications.filter(a => a.status === 'Offer').length,
                    active: state.applications.filter(a => !['Offer', 'Rejected'].includes(a.status)).length,
                    pendingActions: state.applications.filter(a => a.next_action <= today && !['Offer', 'Rejected'].includes(a.status)).length,
                    successRate: total === 0 ? 0 : Math.round((state.applications.filter(a => a.status === 'Offer').length / total) * 100)
                };
            },

            getFilteredApplications: () => {
                return state.applications.filter(a => {
                    const matchS = state.filter.status === 'All' || a.status === state.filter.status;
                    const search = state.filter.search.toLowerCase();
                    const matchT = a.company.toLowerCase().includes(search) || a.role.toLowerCase().includes(search);
                    return matchS && matchT;
                });
            },

            handleFilter: () => {
                state.filter.search = document.getElementById('search-input').value;
                state.filter.status = document.getElementById('status-filter').value;
                ui.renderDashboard();
            },

            updateStatus: async (id, newStatus) => {
                const job = state.applications.find(a => a.id == id);
                if (job) {
                    const updated = { ...job, status: newStatus };
                    await api.update(id, updated);
                    job.status = newStatus;
                    ui.showToast(`Updated ${job.company} status to ${newStatus}`);
                    ui.renderDashboard();
                }
            },

            handleSave: async (e) => {
                e.preventDefault();
                const form = document.getElementById('app-form');
                const data = Object.fromEntries(new FormData(form));
                
                if (data.id) {
                    await api.update(data.id, data);
                    const index = state.applications.findIndex(a => a.id == data.id);
                    state.applications[index] = { ...state.applications[index], ...data };
                    ui.showToast(`${data.company} updated`);
                } else {
                    delete data.id;
                    const result = await api.create(data);
                    await api.getAll();
                    ui.showToast('New application tracked!');
                }
                router.navigate('dashboard');
            },

            deleteConfirm: (id) => {
                state.editingId = id;
                document.getElementById('delete-modal').classList.remove('hidden');
                document.getElementById('confirm-delete-btn').onclick = async () => {
                    const job = state.applications.find(a => a.id === state.editingId);
                    await api.delete(state.editingId);
                    state.applications = state.applications.filter(a => a.id !== state.editingId);
                    ui.closeModal();
                    ui.renderDashboard();
                    ui.showToast(`${job.company} removed from list`);
                };
            }
        };

        document.addEventListener('DOMContentLoaded', async () => {
            await api.getAll();
            router.navigate('dashboard');
        });
    </script>
</body>
</html>