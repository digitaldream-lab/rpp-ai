<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      
      <!-- Top Navbar Dashboard -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-4 border-b border-gray-200">
        <div>
          <h1 class="text-3xl font-extrabold text-gray-900">Dashboard Guru RPP AI</h1>
          <p class="mt-1 text-sm text-gray-500">Kelola kelas, mata pelajaran, materi, serta mulailah membuat RPP AI.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4">
          <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1.5 rounded-full border border-indigo-200">Guru Aktif</span>
          <a href="/logout" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-md transition">Logout</a>
        </div>
      </div>

      <!-- Tab Bar Menu Navigasi (Telah dipisah menjadi 4 bagian menu mandiri) -->
      <div class="flex space-x-1 bg-indigo-900/10 p-1 rounded-xl mb-8 max-w-xl">
        <button 
          v-for="tab in ['buat_kelas', 'buat_mapel', 'upload_materi', 'generate_rpp']" 
          :key="tab"
          @click="activeTab = tab"
          :class="[
            'w-full py-2.5 text-xs sm:text-sm font-semibold rounded-lg transition-all',
            activeTab === tab ? 'bg-white text-indigo-700 shadow' : 'text-gray-600 hover:text-indigo-900 hover:bg-white/50'
          ]"
        >
          <span class="capitalize">{{ tab.replace('_', ' ') }}</span>
        </button>
      </div>

      <!-- Notifikasi Kustom Keren (Menggantikan alert bawaan) -->
      <div v-if="notification.show" :class="['fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-lg text-white z-50 font-semibold transition-all duration-300', notification.isError ? 'bg-red-600' : 'bg-emerald-600']">
        {{ notification.message }}
      </div>

      <!-- Tab 1: Pembuatan Kelas Baru (A1) -->
       <div v-if="activeTab === 'buat_kelas'" class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-xl font-bold text-gray-900 mb-4">A1. Buat Kelas Baru</h2>
        <form @submit.prevent="submitKelas" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Jenjang Kelas</label>
            <input v-model="formKelas.nama_jenjang" type="text" placeholder="Contoh: 1A, 1B, XIA, XIIB" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required />
          </div>
          <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-semibold shadow-md transition">Buat Kelas Baru</button>
        </form>
          <h3 class="text-lg font-bold text-gray-900 mt-8 mb-3">Daftar Kelas</h3>
          <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto">
            <div v-for="item in kelas" :key="item.id" class="py-3 flex justify-between items-center">
              <div v-if="editingId !== item.id">
                <span class="font-medium text-gray-800">Kelas {{ item.nama_jenjang }}</span>
              </div>
              <div v-else class="flex gap-2">
                <input v-model="editForm.nama_jenjang" class="border p-1 rounded text-sm" />
                <button @click="saveEdit(item.id)" class="text-green-600 text-xs font-bold">Simpan</button>
              </div>
              <div class="flex gap-3">
                <button @click="startEdit(item)" class="text-blue-600 text-xs hover:underline">Edit</button>
                <button @click="deleteKelas(item.id)" class="text-red-600 text-xs hover:underline">Hapus</button>
              </div>
            </div>
          </div>
      </div>

      <!-- Tab 2: Pembuatan Mata Pelajaran Baru (A2) -->
      <div v-if="activeTab === 'buat_mapel'" class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
  <h2 class="text-xl font-bold text-gray-900 mb-4">A2. Buat Mata Pelajaran</h2>
  <form @submit.prevent="submitMapel" class="space-y-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Kelas</label>
      <select v-model="formMapel.kelas_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
        <option value="">-- Pilih Kelas --</option>
        <option v-for="item in kelas" :key="item.id" :value="item.id">Kelas {{ item.nama_jenjang }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mata Pelajaran</label>
      <input v-model="formMapel.nama" type="text" placeholder="Contoh: Matematika, IPA" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required />
    </div>
    <div class="flex items-center">
      <input v-model="formMapel.is_agama" id="is_agama" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
      <label for="is_agama" class="ml-2 block text-sm text-gray-900">Apakah ini mata pelajaran agama?</label>
    </div>
    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-semibold shadow-md transition">Buat Mata Pelajaran</button>
  </form>

  <h3 class="text-lg font-bold text-gray-900 mt-8 mb-3">Daftar Mata Pelajaran Terbuat</h3>
  <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto">
    <div v-for="item in mapels" :key="item.id" class="py-3 flex justify-between items-center">
      
      <!-- Mode Tampilan -->
      <div v-if="editingMapelId !== item.id">
        <span class="font-medium text-gray-800">{{ item.nama }}</span>
        <span v-if="item.is_agama" class="ml-2 text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-semibold">Agama</span>
        <span class="ml-2 text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded">Kelas {{ kelas.find(k => k.id === item.kelas_id)?.nama_jenjang }}</span>
      </div>

      <!-- Mode Edit -->
      <div v-else class="flex gap-2">
        <input v-model="editMapelForm.nama" class="border p-1 rounded text-sm w-32" />
        <button @click="saveEditMapel(item.id)" class="text-green-600 text-xs font-bold hover:underline">Simpan</button>
        <button @click="editingMapelId = null" class="text-gray-400 text-xs hover:underline">Batal</button>
      </div>

      <!-- Tombol Aksi -->
      <div v-if="editingMapelId !== item.id" class="flex gap-3">
        <button @click="startEditMapel(item)" class="text-blue-600 text-xs hover:underline">Edit</button>
        <button @click="deleteMapel(item.id)" class="text-red-600 text-xs hover:underline">Hapus</button>
      </div>
    </div>
    <div v-if="mapels.length === 0" class="py-3 text-center text-gray-400 italic">Belum ada mata pelajaran yang dibuat.</div>
  </div>
</div>

      <!-- Tab 3: Upload Materi Pembelajaran (A3) -->
      <div v-if="activeTab === 'upload_materi'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Form Upload Berkas PDF -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-bold text-gray-900 mb-4">A3. Unggah Berkas Materi</h2>
          <form @submit.prevent="submitMateri" class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Kelas</label>
              <select v-model="selectedKelasForMateri" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                <option value="">-- Pilih Kelas --</option>
                <option v-for="item in kelas" :key="item.id" :value="item.id">Kelas {{ item.nama_jenjang }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Mata Pelajaran</label>
              <select v-model="formMateri.mata_pelajaran_id" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                <option value="">-- Pilih Mapel --</option>
                <option v-for="item in filteredMapelsForMateri" :key="item.id" :value="item.id">{{ item.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Berkas Referensi (Format PDF)</label>
              <input type="file" @change="handleFileUpload" accept="application/pdf" class="w-full border border-gray-300 p-2 rounded-lg" required />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Link Referensi Tambahan (Opsional)</label>
              <input v-model="formMateri.referensi_link" type="url" placeholder="[https://sumber-referensi.com](https://sumber-referensi.com)" class="w-full border-gray-300 rounded-lg shadow-sm" />
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg font-semibold shadow transition">Upload Berkas</button>
          </form>
        </div>

        <!-- Tabel Monitoring Berkas Terunggah -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Materi Berhasil Terunggah</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                <tr>
                  <th class="px-6 py-3 text-left">Kelas</th>
                  <th class="px-6 py-3 text-left">Mata Pelajaran</th>
                  <th class="px-6 py-3 text-left">Nama Berkas PDF</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-gray-700">
                <tr v-for="item in materis" :key="item.id">
                  <td class="px-6 py-4 font-semibold">Kelas {{ item.mata_pelajaran?.kelas?.nama_jenjang }}</td>
                  <td class="px-6 py-4">{{ item.mata_pelajaran?.nama }}</td>
                  <td class="px-6 py-4 text-indigo-600 font-medium truncate max-w-xs">
                    <a :href="'/storage/' + item.file_path" target="_blank" class="hover:underline">{{ item.file_path.split('/').pop() }}</a>
                  </td>
                </tr>
                <tr v-if="materis.length === 0">
                  <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">Belum ada berkas materi yang diunggah.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab 4: Form Pemrosesan RPP (A4) -->
      <div v-if="activeTab === 'generate_rpp'" class="max-w-xl mx-auto bg-white p-8 rounded-3xl shadow-md border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-1">Mulai Pemrosesan RPP AI</h2>
        <p class="text-sm text-gray-500 text-center mb-8">AI akan merekomendasikan metode secara otomatis, media interaktif, LKPD siswa, dan evaluasi pembelajaran.</p>

        <form @submit.prevent="submitGenerateRpp" class="space-y-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Jenjang Kelas</label>
            <select v-model="selectedKelasForRpp" class="w-full border-gray-300 rounded-lg shadow-sm" required>
              <option value="">-- Pilih Kelas --</option>
              <option v-for="item in kelas" :key="item.id" :value="item.id">Kelas {{ item.nama_jenjang }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Mata Pelajaran</label>
            <select v-model="formRpp.mata_pelajaran_id" class="w-full border-gray-300 rounded-lg shadow-sm" required>
              <option value="">-- Pilih Mata Pelajaran --</option>
              <option v-for="item in filteredMapelsForRpp" :key="item.id" :value="item.id">{{ item.nama }} {{ item.is_agama ? '(Agama)' : '' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Pertemuan (Pertemuan RPP)</label>
            <input v-model="formRpp.jumlah_pertemuan" type="number" min="1" class="w-full border-gray-300 rounded-lg shadow-sm" required />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Sumber Pengambilan Materi</label>
            <div class="mt-2 space-y-2">
              <label class="flex items-center text-sm text-gray-700 font-medium cursor-pointer">
                <input v-model="formRpp.sumber_materi" type="radio" value="materi" class="h-4 w-4 text-indigo-600 border-gray-300" />
                <span class="ml-2">Dari Berkas Materi yang Diupload (RAG)</span>
              </label>
              <label class="flex items-center text-sm text-gray-700 font-medium cursor-pointer">
                <input v-model="formRpp.sumber_materi" type="radio" value="ai" class="h-4 w-4 text-indigo-600 border-gray-300" />
                <span class="ml-2">Dari Otak AI Murni</span>
              </label>
              <label class="flex items-center text-sm text-gray-700 font-medium cursor-pointer">
                <input v-model="formRpp.sumber_materi" type="radio" value="keduanya" class="h-4 w-4 text-indigo-600 border-gray-300" />
                <span class="ml-2">Kombinasi Materi Upload & AI</span>
              </label>
            </div>
          </div>

          <!-- Loading State Overlay -->
          <div v-if="loadingGenerate" class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl text-indigo-700 flex items-center justify-center gap-3">
            <svg class="animate-spin h-5 w-5 text-indigo-700" xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-semibold">Sedang merancang RPP, LKPD, media interaktif, kurikulum 4C, dan dalil Agama...</span>
          </div>

          <button type="submit" :disabled="loadingGenerate" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg transition duration-200">
            🚀 Generate RPP AI
          </button>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  kelas: Array,
  mapels: Array,
  materis: Array
});

// Set menu aktif awal ke 'buat_kelas'
const activeTab = ref('buat_kelas');
const selectedKelasForMateri = ref('');
const selectedKelasForRpp = ref('');
const loadingGenerate = ref(false);

const editingId = ref(null);
const editForm = useForm({ nama_jenjang: '' });

const startEdit = (item) => {
  editingId.value = item.id;
  editForm.nama_jenjang = item.nama_jenjang;
};

const saveEdit = (id) => {
  editForm.put(route('guru.kelas.update', id), {
    onSuccess: () => { editingId.value = null; }
  });
};

const deleteKelas = (id) => {
  if (confirm('Yakin ingin menghapus kelas ini?')) {
    useForm({}).delete(route('guru.kelas.destroy', id));
  }
};

// Sistem Notifikasi Alternatif alert()
const notification = ref({ show: false, message: '', isError: false });
const showNotification = (message, isError = false) => {
  notification.value = { show: true, message, isError };
  setTimeout(() => {
    notification.value.show = false;
  }, 4000);
};

const formKelas = useForm({ nama_jenjang: '' });
const formMapel = useForm({ kelas_id: '', nama: '', is_agama: false });
const formMateri = useForm({ mata_pelajaran_id: '', file: null, referensi_link: '' });
const formRpp = useForm({ mata_pelajaran_id: '', jumlah_pertemuan: 1, sumber_materi: 'keduanya' });

const filteredMapelsForMateri = computed(() => {
  if (!selectedKelasForMateri.value) return [];
  return props.mapels.filter(m => m.kelas_id === Number(selectedKelasForMateri.value));
});

const filteredMapelsForRpp = computed(() => {
  if (!selectedKelasForRpp.value) return [];
  return props.mapels.filter(m => m.kelas_id === Number(selectedKelasForRpp.value));
});

const submitKelas = () => {
  formKelas.post(route('guru.kelas.store'), {
    onSuccess: () => { 
      formKelas.reset(); 
      showNotification('Kelas baru berhasil disimpan!'); 
    }
  });
};

const submitMapel = () => {
  formMapel.post(route('guru.mapel.store'), {
    onSuccess: () => { 
      formMapel.reset(); 
      showNotification('Mata Pelajaran berhasil didaftarkan!'); 
    }
  });
};

const handleFileUpload = (e) => { formMateri.file = e.target.files[0]; };

const submitMateri = () => {
  formMateri.post(route('guru.materi.store'), {
    onSuccess: () => { 
      formMateri.reset(); 
      selectedKelasForMateri.value = ''; 
      showNotification('Berkas materi PDF berhasil diunggah!'); 
    }
  });
};

const submitGenerateRpp = () => {
  loadingGenerate.value = true;
  formRpp.post(route('rpp.generate'), {
    onFinish: () => { loadingGenerate.value = false; },
    onError: () => {
      showNotification('Gagal memproses RPP dengan AI. Mohon periksa API Key OpenAI di .env.', true);
    }
  });
};

//hapus dan edit mapel
const editingMapelId = ref(null);
const editMapelForm = useForm({ nama: '' });

const startEditMapel = (item) => {
  editingMapelId.value = item.id;
  editMapelForm.nama = item.nama;
};

const saveEditMapel = (id) => {
  editMapelForm.put('/guru/mapel/' + id, {
    onSuccess: () => { 
      editingMapelId.value = null; 
      alert('Mata pelajaran diperbarui!');
    }
  });
};

const deleteMapel = (id) => {
  if (confirm('Yakin ingin menghapus mata pelajaran ini?')) {
    useForm({}).delete('/guru/mapel/' + id, {
      onSuccess: () => alert('Mata pelajaran dihapus!')
    });
  }
};
</script>