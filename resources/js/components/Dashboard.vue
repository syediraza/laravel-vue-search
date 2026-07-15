<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans">

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 py-6 md:px-8 space-y-6">

      <!-- Search & Filters Container -->
      <section class="bg-slate-900/40 border border-slate-850 p-5 rounded-2xl space-y-4 shadow-xl">
        <!-- Search Input -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Search class="w-4.5 h-4.5" />
          </div>
          <input 
            v-model="searchInput" 
            type="text" 
            placeholder="Search campaigns by name or description..." 
            class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-colors"
          />
          <button 
            v-if="searchInput" 
            @click="searchInput = ''" 
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-350 cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

      </section>

      <!-- Campaigns List Card View & Table -->
      <section class="bg-slate-900/25 border border-slate-850 rounded-2xl overflow-hidden shadow-md">
        
        <!-- Table Loader Skeleton -->
        <div v-if="loading" class="p-6 space-y-4">
          <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="h-5 w-40 bg-slate-800 rounded animate-pulse"></div>
            <div class="h-5 w-20 bg-slate-800 rounded animate-pulse"></div>
          </div>
          <div v-for="i in 5" :key="i" class="grid grid-cols-6 gap-4 py-3 items-center">
            <div class="col-span-2 h-4 bg-slate-800 rounded animate-pulse"></div>
            <div class="h-4 bg-slate-800 rounded animate-pulse"></div>
            <div class="h-4 bg-slate-800 rounded animate-pulse"></div>
            <div class="h-4 bg-slate-800 rounded animate-pulse"></div>
            <div class="h-4 bg-slate-800 rounded animate-pulse justify-self-end"></div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="campaigns.length === 0" class="p-12 text-center flex flex-col items-center justify-center space-y-4">
          <div class="w-16 h-16 rounded-full bg-slate-900/60 border border-slate-800 flex items-center justify-center text-slate-500 mx-auto">
            <Search class="w-8 h-8" />
          </div>
          <div class="max-w-md mx-auto">
            <h3 class="text-base font-semibold text-slate-200">No campaigns found</h3>
            <p class="text-sm text-slate-400 mt-1">We couldn't find any campaign matching your criteria. Try adjusting your filters or search keywords.</p>
          </div>
          <button 
            @click="resetFilters" 
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-sm font-semibold transition-all cursor-pointer"
          >
            Clear Filters
          </button>
        </div>

        <div v-else>
          <!-- Desktop Grid / Table -->
          <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-800/80 bg-slate-900/40 text-slate-400 text-xs font-semibold tracking-wider">
                <th class="py-4 px-6">Campaign Name</th>
                <th class="py-4 px-6">Status</th>
                <th class="py-4 px-6">Type</th>
                <th class="py-4 px-6">Budget / Spent</th>
                <th class="py-4 px-6">Performance CTR</th>
                <th class="py-4 px-6">Duration</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50 bg-slate-900/10">
              <tr 
                v-for="campaign in campaigns" 
                :key="campaign.id" 
                class="hover:bg-slate-900/30 group transition-all"
              >
                <!-- Name -->
                <td class="py-4 px-6">
                  <div class="font-medium text-slate-100 group-hover:text-violet-300 transition-colors">{{ campaign.name }}</div>
                  <div class="text-xs text-slate-400 mt-0.5 line-clamp-1 max-w-xs">{{ campaign.description || 'No description provided.' }}</div>
                </td>
                
                <!-- Status Badge -->
                <td class="py-4 px-6 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border"
                    :class="getStatusBadgeClass(campaign.status)"
                  >
                    {{ capitalize(campaign.status) }}
                  </span>
                </td>
                
                <!-- Type -->
                <td class="py-4 px-6 whitespace-nowrap text-slate-300 text-sm">
                  {{ campaign.type }}
                </td>
                
                <!-- Budget / Spent -->
                <td class="py-4 px-6">
                  <div class="flex justify-between items-center text-xs text-slate-300 mb-1 max-w-[150px]">
                    <span class="font-semibold text-slate-100">${{ formatNumber(campaign.spent) }}</span>
                    <span class="text-slate-400">/ ${{ formatNumber(campaign.budget) }}</span>
                  </div>
                  <!-- Progress Bar -->
                  <div class="w-full bg-slate-800 rounded-full h-1.5 max-w-[150px] overflow-hidden">
                    <div 
                      class="h-full rounded-full transition-all duration-500" 
                      :class="getSpendProgressColor(campaign)"
                      :style="{ width: Math.min(calculatePercentage(campaign.spent, campaign.budget), 100) + '%' }"
                    ></div>
                  </div>
                </td>
                
                <!-- CTR & conversions -->
                <td class="py-4 px-6 whitespace-nowrap">
                  <div class="text-sm font-semibold text-slate-200">{{ calculateCTR(campaign.clicks, campaign.impressions) }}%</div>
                  <div class="text-[10px] text-slate-400 mt-0.5">{{ formatNumber(campaign.clicks) }} clicks · {{ formatNumber(campaign.conversions) }} conv.</div>
                </td>
                
                <!-- Duration -->
                <td class="py-4 px-6 whitespace-nowrap text-xs text-slate-300">
                  <div class="flex items-center gap-1">
                    <Calendar class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ formatDate(campaign.start_date) }}</span>
                  </div>
                  <div class="text-[10px] text-slate-400 pl-4.5 mt-0.5">
                    to {{ campaign.end_date ? formatDate(campaign.end_date) : 'Ongoing' }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Stacked Card List -->
        <div class="md:hidden divide-y divide-slate-800 bg-slate-900/10">
          <div 
            v-for="campaign in campaigns" 
            :key="campaign.id" 
            class="p-4 space-y-3"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <h4 class="font-medium text-slate-100 text-sm leading-snug">{{ campaign.name }}</h4>
                <span class="inline-block px-2 py-0.5 rounded bg-slate-800/80 text-[10px] font-semibold text-slate-400 mt-1 mr-2">{{ campaign.type }}</span>
                <span 
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border"
                  :class="getStatusBadgeClass(campaign.status)"
                >
                  {{ capitalize(campaign.status) }}
                </span>
              </div>
            </div>

            <!-- Description -->
            <p v-if="campaign.description" class="text-xs text-slate-400 line-clamp-2">{{ campaign.description }}</p>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 gap-3 pt-1">
              <div>
                <div class="text-[10px] text-slate-400">Budget / Spent:</div>
                <div class="text-xs text-slate-200 mt-0.5 font-medium">
                  ${{ formatNumber(campaign.spent) }} / ${{ formatNumber(campaign.budget) }}
                </div>
                <div class="w-full bg-slate-800 rounded-full h-1 mt-1 overflow-hidden">
                  <div 
                    class="h-full rounded-full" 
                    :class="getSpendProgressColor(campaign)"
                    :style="{ width: Math.min(calculatePercentage(campaign.spent, campaign.budget), 100) + '%' }"
                  ></div>
                </div>
              </div>
              <div>
                <div class="text-[10px] text-slate-400">Performance:</div>
                <div class="text-xs text-slate-200 mt-0.5 font-medium">
                  {{ calculateCTR(campaign.clicks, campaign.impressions) }}% CTR <span class="text-slate-500 font-normal">({{ formatNumber(campaign.clicks) }} clicks)</span>
                </div>
              </div>
            </div>

            <!-- Dates -->
            <div class="text-[10px] text-slate-500 flex items-center gap-1 pt-1">
              <Calendar class="w-3.5 h-3.5 text-slate-600" />
              <span>{{ formatDate(campaign.start_date) }} to {{ campaign.end_date ? formatDate(campaign.end_date) : 'Ongoing' }}</span>
            </div>
          </div>
        </div>

        <!-- Pagination Bar -->
        <div class="border-t border-slate-800 bg-slate-950/40 px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-400">
          <div>
            Showing 
            <span class="font-semibold text-slate-350">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span>
            to 
            <span class="font-semibold text-slate-350">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
            of 
            <span class="font-semibold text-slate-350">{{ pagination.total }}</span>
            campaigns
          </div>

          <div class="flex items-center gap-1">
            <button 
              @click="goToPage(pagination.current_page - 1)" 
              :disabled="pagination.current_page === 1"
              class="p-2 border border-slate-800 rounded-lg hover:bg-slate-900 hover:text-slate-200 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-slate-400 transition-colors cursor-pointer"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
            
            <button 
              v-for="page in visiblePages" 
              :key="page"
              @click="goToPage(page)" 
              class="w-8 h-8 rounded-lg border text-center transition-colors cursor-pointer font-medium"
              :class="page === pagination.current_page 
                ? 'bg-violet-600 border-violet-500 text-white shadow-sm' 
                : 'border-slate-800 hover:bg-slate-900 hover:text-slate-200 text-slate-400'"
            >
              {{ page }}
            </button>
            
            <button 
              @click="goToPage(pagination.current_page + 1)" 
              :disabled="pagination.current_page === pagination.last_page"
              class="p-2 border border-slate-800 rounded-lg hover:bg-slate-900 hover:text-slate-200 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-slate-400 transition-colors cursor-pointer"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      </section>
    </main>

  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { 
  Search, Calendar, 
  ChevronLeft, ChevronRight, X 
} from '@lucide/vue';

// State Variables
const campaigns = ref([]);
const loading = ref(false);
const searchInput = ref('');
const debouncedSearch = ref('');

// Filter tag lists
const selectedStatuses = ref([]);

// Pagination State
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
});


// Debounce timer
let debounceTimeout = null;

// Watch searchInput to perform debouncing
watch(searchInput, (newValue) => {
  if (debounceTimeout) clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    debouncedSearch.value = newValue;
  }, 350);
});

// Watch reactive filters to fetch campaigns dynamically
watch(debouncedSearch, () => {
  goToPage(1); // Reset to page 1 on filter changes
});

// Watchers for status array filters
watch(selectedStatuses, () => {
  goToPage(1);
}, { deep: true });

// Page Range Generator for Pagination Numbers
const visiblePages = computed(() => {
  const range = [];
  const start = Math.max(1, pagination.current_page - 2);
  const end = Math.min(pagination.last_page, pagination.current_page + 2);
  for (let i = start; i <= end; i++) {
    range.push(i);
  }
  return range;
});

// Axios calls configuration
const fetchCampaigns = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
    };

    if (debouncedSearch.value) params.search = debouncedSearch.value;
    
    // Pass status array filters if selected
    if (selectedStatuses.value.length > 0) params.status = selectedStatuses.value;

    const response = await axios.get('/api/campaigns', { params });
    const { data, meta } = response.data;
    
    campaigns.value = data;
    
    // Map pagination
    pagination.current_page = meta.current_page;
    pagination.last_page = meta.last_page;
    pagination.per_page = meta.per_page;
    pagination.total = meta.total;
  } catch (error) {
    console.error('Error fetching campaigns:', error);
  } finally {
    loading.value = false;
  }
};

// Pagination execution
const goToPage = (page) => {
  if (page < 1 || page > pagination.last_page) return;
  fetchCampaigns(page);
};

const resetFilters = () => {
  searchInput.value = '';
};






// Formatting helpers
const formatNumber = (num) => {
  if (num === undefined || num === null) return '0';
  return Number(num).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const calculatePercentage = (value, total) => {
  if (!total || total <= 0) return 0;
  return Math.round((value / total) * 100);
};

const calculateCTR = (clicks, impressions) => {
  if (!impressions || impressions <= 0) return '0.00';
  return ((clicks / impressions) * 100).toFixed(2);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
};

const capitalize = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
};

// Class mapping helpers
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-emerald-950/30 border-emerald-500/20 text-emerald-400';
    case 'paused':
      return 'bg-amber-950/30 border-amber-500/20 text-amber-400';
    case 'completed':
      return 'bg-blue-950/30 border-blue-500/20 text-blue-400';
    case 'draft':
      return 'bg-slate-900 border-slate-800 text-slate-400';
    default:
      return 'bg-slate-900 border-slate-800 text-slate-400';
  }
};



const getSpendProgressColor = (campaign) => {
  const pct = calculatePercentage(campaign.spent, campaign.budget);
  if (pct > 100) return 'bg-rose-500';
  if (pct > 90) return 'bg-amber-500';
  return 'bg-emerald-500';
};

// Lifecycle
onMounted(() => {
  fetchCampaigns();
});
</script>


