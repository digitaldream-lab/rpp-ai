<template>
  <div class="min-h-screen bg-gray-50 text-gray-800 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      
      <!-- Top Navbar -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 border-b border-gray-200 pb-6">
        <div>
          <span class="text-indigo-600 font-extrabold uppercase tracking-wider text-xs">Aplikasi RPP AI</span>
          <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 mt-1">Dashboard Guru</h1>
          <p class="text-sm text-gray-500 mt-1">Kelola kelas, mata pelajaran, materi, dan buat RPP otomatis.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4">
          <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">Akses Guru</span>
          <a href="/logout" class="bg-red-500 hover:bg-red-600 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition">Logout</a>
        </div>
      </div>

      <!-- Toast Notification -->
      <transition name="fade">
        <div v-if="notification.show" :class="['fixed top-10 right-10 px-6 py-4 rounded-xl shadow-2xl text-white z-50 font-semibold border', notification.isError ? 'bg-red-600 border-red-700' : 'bg-emerald-500 border-emerald-600']">
          <div class="flex items-center gap-3">
            <span v-if="notification.isError">⚠️</span>
            <span v-else>✅</span>
            {{ notification.message }}
          </div>
        </div>
      </transition>

      <!-- Tab Navigation -->
      <div class="flex flex-wrap gap-2 bg-white border border-gray-200 p-2 rounded-2xl mb-8 shadow-sm max-w-3xl">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="['flex-1 py-2.5 px-4 text-sm font-semibold rounded-xl transition-all whitespace-nowrap', activeTab === tab.id ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-indigo-600 hover:bg-indigo-50']">
          {{ tab.label }}
        </button>
      </div>

      <!-- ========================================== -->
      <!-- TAB 1: KELOLA KELAS -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'kelas'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in">
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-gray-100 h-fit">
          <h2 class="text-xl font-bold mb-6 text-gray-800">{{ isEditingKelas ? 'Edit Kelas' : 'Buat Kelas Baru' }}</h2>
          <form @submit.prevent="submitKelas" class="space-y-4 text-sm">
            <div>
              <label class="block text-gray-600 mb-1 font-semibold">Nama/Tingkat Kelas</label>
              <input v-model="formKelas.nama_jenjang" placeholder="Contoh: X IPA 1" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 outline-none" required />
            </div>
            <div class="flex gap-2">
              <button type="submit" :disabled="formKelas.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition disabled:opacity-50">
                {{ isEditingKelas ? 'Simpan Edit' : 'Buat Kelas' }}
              </button>
              <button v-if="isEditingKelas" @click="cancelEditKelas" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-3 rounded-xl font-bold transition">Batal</button>
            </div>
          </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-bold mb-6 text-gray-800">Daftar Kelas Saya</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr><th class="px-4 py-3 rounded-tl-lg">Nama Kelas</th><th class="px-4 py-3 text-center rounded-tr-lg w-32">Aksi</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in kelas" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 font-bold text-gray-800 text-base">Kelas {{ item.nama_jenjang }}</td>
                  <td class="px-4 py-4 text-center">
                    <button @click="editKelas(item)" class="text-indigo-600 hover:underline font-semibold mr-3">Edit</button>
                    <button @click="deleteKelas(item.id)" class="text-red-500 hover:underline font-semibold">Hapus</button>
                  </td>
                </tr>
                <tr v-if="kelas.length === 0"><td colspan="2" class="text-center py-8 text-gray-400 italic">Belum ada kelas.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 2: KELOLA MAPEL -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'mapel'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in">
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-gray-100 h-fit">
          <h2 class="text-xl font-bold mb-6 text-gray-800">{{ isEditingMapel ? 'Edit Mata Pelajaran' : 'Buat Mata Pelajaran' }}</h2>
          <form @submit.prevent="submitMapel" class="space-y-4 text-sm">
            <div>
              <label class="block text-gray-600 mb-1 font-semibold">Pilih Kelas</label>
              <select v-model="formMapel.kelas_id" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 outline-none" required>
                <option value="" disabled>-- Pilih Kelas --</option>
                <option v-for="k in kelas" :key="k.id" :value="k.id">Kelas {{ k.nama_jenjang }}</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-600 mb-1 font-semibold">Nama Mata Pelajaran</label>
              <input v-model="formMapel.nama" placeholder="Contoh: Matematika Lanjut" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 outline-none" required />
            </div>
            <label class="flex items-center space-x-3 cursor-pointer bg-indigo-50 p-3 rounded-lg border border-indigo-100">
              <input type="checkbox" v-model="formMapel.is_agama" class="form-checkbox h-5 w-5 text-indigo-600 rounded">
              <span class="text-indigo-900 font-medium text-sm">Centang jika ini pelajaran Agama Islam (AI akan memuat dalil-dalil)</span>
            </label>
            <div class="flex gap-2">
              <button type="submit" :disabled="formMapel.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition disabled:opacity-50">
                {{ isEditingMapel ? 'Simpan Edit' : 'Buat Mapel' }}
              </button>
              <button v-if="isEditingMapel" @click="cancelEditMapel" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-3 rounded-xl font-bold transition">Batal</button>
            </div>
          </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-bold mb-6 text-gray-800">Daftar Mata Pelajaran</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr><th class="px-4 py-3 rounded-tl-lg">Kelas</th><th class="px-4 py-3">Nama Mapel</th><th class="px-4 py-3 text-center">Tipe</th><th class="px-4 py-3 text-center rounded-tr-lg w-32">Aksi</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in mapels" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4 font-semibold text-gray-700">Kelas {{ kelas.find(k => k.id === item.kelas_id)?.nama_jenjang }}</td>
                  <td class="px-4 py-4 font-bold text-gray-900">{{ item.nama }}</td>
                  <td class="px-4 py-4 text-center">
                    <span v-if="item.is_agama" class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-md text-xs font-bold">Agama</span>
                    <span v-else class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-bold">Umum</span>
                  </td>
                  <td class="px-4 py-4 text-center whitespace-nowrap">
                    <button @click="editMapel(item)" class="text-indigo-600 hover:underline font-semibold mr-3">Edit</button>
                    <button @click="deleteMapel(item.id)" class="text-red-500 hover:underline font-semibold">Hapus</button>
                  </td>
                </tr>
                <tr v-if="mapels.length === 0"><td colspan="4" class="text-center py-8 text-gray-400 italic">Belum ada mata pelajaran.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: UPLOAD MATERI -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'materi'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in">
        <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-gray-100 h-fit">
          <h2 class="text-xl font-bold mb-6 text-gray-800">{{ isEditingMateri ? 'Edit Materi' : 'Unggah Materi PDF' }}</h2>
          <form @submit.prevent="submitMateri" class="space-y-4 text-sm">
            <div>
              <label class="block text-gray-600 mb-1 font-semibold">Pilih Mata Pelajaran</label>
              <select v-model="formMateri.mata_pelajaran_id" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 outline-none" required>
                <option value="" disabled>-- Pilih Mapel --</option>
                <option v-for="m in mapels" :key="m.id" :value="m.id">Kls {{ kelas.find(k => k.id === m.kelas_id)?.nama_jenjang }} - {{ m.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block text-gray-600 mb-1 font-semibold">{{ isEditingMateri ? 'Ganti File PDF (Opsional)' : 'Pilih File PDF' }}</label>
              <input type="file" @change="handleFileUpload" accept="application/pdf,.doc,.docx" class="w-full border border-gray-300 p-2 rounded-lg bg-gray-50 text-gray-600" :required="!isEditingMateri" />
            </div>
            <div>
              <label class="block text-gray-600 mb-1 font-semibold">Link Video/Referensi (Opsional)</label>
              <input v-model="formMateri.referensi_link" type="url" placeholder="https://youtube.com/..." class="w-full bg-gray-50 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-indigo-500 outline-none" />
            </div>
            <div class="flex gap-2 pt-2">
              <button type="submit" :disabled="formMateri.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold transition disabled:opacity-50">
                {{ isEditingMateri ? 'Simpan Edit' : 'Unggah Materi' }}
              </button>
              <button v-if="isEditingMateri" @click="cancelEditMateri" type="button" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-3 rounded-xl font-bold transition">Batal</button>
            </div>
          </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
          <h2 class="text-xl font-bold mb-6 text-gray-800">Daftar Materi Ajar</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr><th class="px-4 py-3 rounded-tl-lg">Mapel</th><th class="px-4 py-3">File Berkas</th><th class="px-4 py-3 text-center rounded-tr-lg w-32">Aksi</th></tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in materis" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-4 py-4">
                    <p class="font-bold text-gray-900">{{ item.mata_pelajaran?.nama }}</p>
                    <p class="text-xs text-gray-500">Kelas {{ item.mata_pelajaran?.kelas?.nama_jenjang }}</p>
                  </td>
                  <td class="px-4 py-4">
                    <a :href="'/storage/' + item.file_path" target="_blank" class="text-indigo-600 font-medium hover:underline flex items-center gap-1">
                      📄 {{ item.file_path.split('/').pop() }}
                    </a>
                    <a v-if="item.referensi_link" :href="item.referensi_link" target="_blank" class="text-blue-500 text-xs hover:underline mt-1 block">🔗 Lihat Referensi</a>
                  </td>
                  <td class="px-4 py-4 text-center whitespace-nowrap">
                    <button @click="editMateri(item)" class="text-indigo-600 hover:underline font-semibold mr-3">Edit</button>
                    <button @click="deleteMateri(item.id)" class="text-red-500 hover:underline font-semibold">Hapus</button>
                  </td>
                </tr>
                <tr v-if="materis.length === 0"><td colspan="3" class="text-center py-8 text-gray-400 italic">Belum ada materi terunggah.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 4: GENERATE RPP (AI) -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'rpp'" class="animate-fade-in max-w-2xl mx-auto">
        <div class="bg-white p-8 rounded-3xl shadow-xl shadow-indigo-100 border border-indigo-50">
          <div class="text-center mb-8">
            <span class="text-5xl">✨</span>
            <h2 class="text-2xl font-extrabold text-gray-900 mt-4">AI RPP Generator</h2>
            <p class="text-gray-500 text-sm mt-2">Buat modul ajar lengkap secara instan dan otomatis menggunakan Kecerdasan Buatan.</p>
          </div>
          
          <form @submit.prevent="submitRpp" class="space-y-6">
            <div>
              <label class="block text-gray-700 font-bold mb-2">Pilih Mata Pelajaran</label>
              <select v-model="formRpp.mata_pelajaran_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-gray-800 focus:ring-2 focus:ring-indigo-500 outline-none" required>
                <option value="" disabled>-- Tentukan Mapel & Kelas --</option>
                <option v-for="m in mapels" :key="m.id" :value="m.id">Kelas {{ kelas.find(k => k.id === m.kelas_id)?.nama_jenjang }} - {{ m.nama }}</option>
              </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-gray-700 font-bold mb-2">Jumlah Pertemuan</label>
                <input v-model="formRpp.jumlah_pertemuan" type="number" min="1" max="14" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-gray-800 text-center font-bold focus:ring-2 focus:ring-indigo-500 outline-none" required />
              </div>
              <div>
                <label class="block text-gray-700 font-bold mb-2">Sumber Materi</label>
                <select v-model="formRpp.sumber_materi" class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-gray-800 focus:ring-2 focus:ring-indigo-500 outline-none">
                  <option value="ai">Bebas dari AI</option>
                  <option value="materi">Sesuai PDF Saya</option>
                  <option value="keduanya">Kombinasi AI & PDF</option>
                </select>
              </div>
            </div>

            <button type="submit" :disabled="formRpp.processing" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-extrabold text-lg shadow-lg hover:shadow-indigo-500/30 transition-all duration-300 disabled:opacity-70 disabled:cursor-wait flex justify-center items-center gap-2">
              <span v-if="formRpp.processing" class="animate-spin text-xl">⏳</span>
              {{ formRpp.processing ? 'AI Sedang Merumuskan RPP...' : 'Generate RPP Sekarang' }}
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  kelas: Array,
  mapels: Array,
  materis: Array
});

const tabs = [
  { id: 'kelas', label: '1. Kelola Kelas' },
  { id: 'mapel', label: '2. Mata Pelajaran' },
  { id: 'materi', label: '3. Upload Materi' },
  { id: 'rpp', label: '✨ Buat RPP AI' }
];

const activeTab = ref('kelas');
const notification = ref({ show: false, message: '', isError: false });

const showNotification = (msg, isError = false) => {
  notification.value = { show: true, message: msg, isError };
  setTimeout(() => notification.value.show = false, 4000);
};

// ==========================================
// 1. KELOLA KELAS
// ==========================================
const isEditingKelas = ref(false);
const editKelasId = ref(null);
const formKelas = useForm({ nama_jenjang: '' });

const submitKelas = () => {
  if (isEditingKelas.value) {
    formKelas.put('/guru/kelas/' + editKelasId.value, {
      preserveScroll: true,
      onSuccess: () => { cancelEditKelas(); showNotification('Kelas diperbarui!'); },
      onError: (e) => showNotification('Gagal: ' + Object.values(e)[0], true)
    });
  } else {
    formKelas.post('/guru/kelas', {
      preserveScroll: true,
      onSuccess: () => { formKelas.reset(); showNotification('Kelas ditambahkan!'); },
      onError: (e) => showNotification('Gagal: ' + Object.values(e)[0], true)
    });
  }
};
const editKelas = (k) => { isEditingKelas.value = true; editKelasId.value = k.id; formKelas.nama_jenjang = k.nama_jenjang; };
const cancelEditKelas = () => { isEditingKelas.value = false; editKelasId.value = null; formKelas.reset(); };
const deleteKelas = (id) => {
  if (confirm('Yakin menghapus kelas ini? Mapel terkait juga akan hilang.')) {
    useForm({}).delete('/guru/kelas/' + id, { onSuccess: () => showNotification('Kelas dihapus!') });
  }
};

// ==========================================
// 2. KELOLA MAPEL
// ==========================================
const isEditingMapel = ref(false);
const editMapelId = ref(null);
const formMapel = useForm({ kelas_id: '', nama: '', is_agama: false });

const submitMapel = () => {
  if (isEditingMapel.value) {
    formMapel.put('/guru/mapel/' + editMapelId.value, {
      preserveScroll: true,
      onSuccess: () => { cancelEditMapel(); showNotification('Mapel diperbarui!'); }
    });
  } else {
    formMapel.post('/guru/mapel', {
      preserveScroll: true,
      onSuccess: () => { formMapel.reset('nama', 'is_agama'); showNotification('Mapel ditambahkan!'); }
    });
  }
};
const editMapel = (m) => { isEditingMapel.value = true; editMapelId.value = m.id; formMapel.kelas_id = m.kelas_id; formMapel.nama = m.nama; formMapel.is_agama = m.is_agama ? true : false; };
const cancelEditMapel = () => { isEditingMapel.value = false; editMapelId.value = null; formMapel.reset(); };
const deleteMapel = (id) => {
  if (confirm('Yakin menghapus mapel ini?')) useForm({}).delete('/guru/mapel/' + id, { onSuccess: () => showNotification('Mapel dihapus!') });
};

// ==========================================
// 3. KELOLA MATERI (DENGAN FILE UPLOAD)
// ==========================================
const isEditingMateri = ref(false);
const editMateriId = ref(null);
// Catatan Penting: Form upload materi yang diedit HARUS ditambahkan properti "_method: 'put'" 
// karena browser tidak bisa mengirim multipart form via PUT asli. Kita akali menggunakan POST ke endpoint UPDATE.
const formMateri = useForm({ _method: 'post', mata_pelajaran_id: '', file: null, referensi_link: '' });

const handleFileUpload = (e) => formMateri.file = e.target.files[0];

const submitMateri = () => {
  if (isEditingMateri.value) {
    formMateri._method = 'put'; // Override ke PUT agar diurus oleh Laravel
    formMateri.post('/guru/materi/' + editMateriId.value, { // Tetap gunakan .post untuk upload file
      preserveScroll: true,
      onSuccess: () => { cancelEditMateri(); showNotification('Materi berhasil diperbarui!'); },
      onError: (e) => showNotification('Gagal: ' + Object.values(e)[0], true)
    });
  } else {
    formMateri._method = 'post';
    formMateri.post('/guru/materi', {
      preserveScroll: true,
      onSuccess: () => { 
        formMateri.reset(); 
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';
        showNotification('Materi diunggah!'); 
      },
      onError: (e) => showNotification('Gagal: ' + Object.values(e)[0], true)
    });
  }
};

const editMateri = (m) => { 
  isEditingMateri.value = true; 
  editMateriId.value = m.id; 
  formMateri.mata_pelajaran_id = m.mata_pelajaran_id; 
  formMateri.referensi_link = m.referensi_link || ''; 
  formMateri.file = null; 
};
const cancelEditMateri = () => { 
  isEditingMateri.value = false; 
  editMateriId.value = null; 
  formMateri.reset(); 
  formMateri._method = 'post';
};
const deleteMateri = (id) => {
  if (confirm('Hapus materi? Berkas PDF juga akan ikut terhapus di server.')) {
    useForm({}).delete('/guru/materi/' + id, { onSuccess: () => showNotification('Materi dihapus!') });
  }
};

// ==========================================
// 4. GENERATE RPP AI
// ==========================================
const formRpp = useForm({ mata_pelajaran_id: '', jumlah_pertemuan: 1, sumber_materi: 'ai' });

const submitRpp = () => {
  formRpp.post('/rpp/generate', {
    preserveScroll: true,
    onError: (e) => showNotification('Gagal: ' + Object.values(e)[0], true)
  });
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>