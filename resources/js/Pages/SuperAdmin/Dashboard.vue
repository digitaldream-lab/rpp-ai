<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 border-b border-slate-800 pb-6">
        <div>
          <span class="text-indigo-400 font-extrabold uppercase tracking-wider text-xs">Konsol Kontrol Utama</span>
          <h1 class="text-4xl font-extrabold tracking-tight text-white mt-1">Superadmin Console</h1>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4">
          <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">Akses Penuh</span>
          <a href="/logout" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-md transition">Logout</a>
        </div>
      </div>

      <!-- Custom Toast Notification -->
      <transition name="fade">
        <div v-if="notification.show" :class="['fixed top-10 right-10 px-6 py-4 rounded-xl shadow-2xl text-white z-50 font-semibold border', notification.isError ? 'bg-red-900/90 border-red-500' : 'bg-emerald-900/90 border-emerald-500']">
          <div class="flex items-center gap-3">
            <span v-if="notification.isError">⚠️</span>
            <span v-else>✅</span>
            {{ notification.message }}
          </div>
        </div>
      </transition>

      <!-- Navigation Tabs -->
      <div class="flex space-x-2 bg-slate-900 border border-slate-800 p-1.5 rounded-xl mb-8 max-w-xl">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="['w-full py-2.5 text-sm font-semibold rounded-lg transition-all', activeTab === tab.id ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800']">
          {{ tab.label }}
        </button>
      </div>

      <!-- ========================================== -->
      <!-- TAB 1: BATASAN 4C -->
      <!-- ========================================== -->
      <div v-if="activeTab === '4c'" class="space-y-8 animate-fade-in">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div v-for="cat in ['Creativity', 'Critical Thinking', 'Communication', 'Collaboration']" :key="cat" @click="select4C(cat)"
            :class="['p-6 rounded-2xl border transition-all cursor-pointer h-32 flex flex-col justify-between', form4C.kategori === cat ? 'bg-indigo-900/30 border-indigo-500' : 'bg-slate-900 border-slate-800 hover:border-slate-700']">
            <h3 class="font-bold text-lg">{{ cat }}</h3>
            <span class="text-xs text-indigo-400 font-bold">{{ props.fourCs.filter(i => i.kategori === cat).length }} Parameter Aktif</span>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Form Input 4C -->
          <div class="lg:col-span-1 bg-slate-900 border border-slate-800 p-6 rounded-3xl h-fit">
            <h3 class="text-xl font-bold mb-6">
              {{ isEditing4C ? 'Edit Batasan:' : 'Buat Batasan:' }} <span class="text-indigo-400">{{ form4C.kategori }}</span>
            </h3>
            <form @submit.prevent="submit4C" class="space-y-4">
              <textarea v-model="form4C.batasan_deskripsi" rows="5" class="w-full bg-slate-950 border-slate-700 rounded-xl p-4 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ketik batasan kurikulum untuk AI di sini..." required></textarea>
              
              <div class="flex gap-2 pt-2">
                <button type="submit" :disabled="form4C.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 py-3 rounded-xl font-bold transition disabled:opacity-50">
                  {{ isEditing4C ? 'Simpan Edit' : 'Tambah Parameter' }}
                </button>
                <button v-if="isEditing4C" @click="cancelEdit4C" type="button" class="px-4 bg-slate-800 hover:bg-slate-700 py-3 rounded-xl font-bold transition">
                  Batal
                </button>
              </div>
            </form>
          </div>

          <!-- Tabel Daftar 4C -->
          <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-3xl overflow-hidden">
            <h2 class="text-xl font-bold mb-6">Daftar Batasan Aktif: <span class="text-indigo-400">{{ form4C.kategori }}</span></h2>
            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-950">
                  <tr>
                    <th class="px-4 py-3 rounded-tl-lg">Kategori</th>
                    <th class="px-4 py-3 w-1/2">Deskripsi Batasan</th>
                    <th class="px-4 py-3 text-center rounded-tr-lg">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                  <tr v-for="item in props.fourCs.filter(i => i.kategori === form4C.kategori)" :key="item.id" class="hover:bg-slate-800/50">
                    <td class="px-4 py-4 text-indigo-400 font-bold align-top">{{ item.kategori }}</td>
                    <td class="px-4 py-4 text-slate-300 align-top">{{ item.batasan_deskripsi }}</td>
                    <td class="px-4 py-4 text-center align-top whitespace-nowrap">
                      <button @click="edit4C(item)" class="text-emerald-400 hover:text-emerald-300 font-medium mr-3">Edit</button>
                      <button @click="delete4C(item.id)" class="text-red-400 hover:text-red-300 font-medium">Hapus</button>
                    </td>
                  </tr>
                  <tr v-if="props.fourCs.filter(i => i.kategori === form4C.kategori).length === 0">
                    <td colspan="3" class="text-center py-12 text-slate-500 italic">Belum ada parameter yang didaftarkan untuk kategori ini.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 2: KELOLA DALIL -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'dalil'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-fade-in">
        
        <div class="lg:col-span-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl h-fit">
          <h2 class="text-xl font-bold mb-6">Daftarkan Dalil</h2>
          <form @submit.prevent="submitDalil" class="space-y-4 text-sm">
            <div>
              <label class="block text-slate-400 mb-1 text-xs">1. Kategori</label>
              <select v-model="formDalil.kategori" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" required>
                <option value="Al-Quran">Al-Quran</option>
                <option value="Hadis">Hadis</option>
              </select>
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">2. Referensi / Sumber</label>
              <input v-model="formDalil.referensi" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" placeholder="Contoh: QS. Al-Baqarah: 151" required />
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">3. Arti Terjemahan</label>
              <textarea v-model="formDalil.arti" rows="3" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" placeholder="Arti dari ayat/hadis..." required></textarea>
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">4. Deskripsi / Tafsir</label>
              <textarea v-model="formDalil.deskripsi" rows="2" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" placeholder="Penjelasan tambahan (opsional)"></textarea>
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">5. Kata Kunci (Keyword)</label>
              <input v-model="formDalil.keyword" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" placeholder="Contoh: wudhu, shalat, kebersihan" required />
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">6. Unggah Gambar Ayat (Opsional)</label>
              <input type="file" @change="handleImageUpload" accept="image/*" class="w-full bg-slate-950 border-slate-700 rounded-lg p-1.5 text-slate-400 text-sm" />
            </div>

            <button type="submit" :disabled="formDalil.processing" class="w-full bg-indigo-600 hover:bg-indigo-700 py-3 rounded-xl font-bold mt-4 transition disabled:opacity-50">
               {{ formDalil.processing ? 'Menyimpan...' : 'Simpan Dalil' }}
            </button>
          </form>
        </div>

        <div class="lg:col-span-8 bg-slate-900 border border-slate-800 p-6 rounded-3xl overflow-hidden">
          <h2 class="text-xl font-bold mb-6">Database Dalil Terverifikasi</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-slate-500 uppercase bg-slate-950">
                <tr>
                  <th class="px-4 py-3 rounded-tl-lg">Kategori</th>
                  <th class="px-4 py-3">Referensi</th>
                  <th class="px-4 py-3 min-w-[200px]">Arti & Deskripsi</th>
                  <th class="px-4 py-3">Kata Kunci</th>
                  <th class="px-4 py-3 text-center rounded-tr-lg">Gambar</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr v-for="item in dalils" :key="item.id" class="hover:bg-slate-800/50">
                  <td class="px-4 py-4 align-top">
                    <span :class="['px-2 py-1 rounded text-xs font-bold whitespace-nowrap', item.kategori === 'Al-Quran' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400']">
                      {{ item.kategori }}
                    </span>
                  </td>
                  <td class="px-4 py-4 font-bold text-white whitespace-nowrap align-top">{{ item.referensi }}</td>
                  <td class="px-4 py-4 align-top">
                    <p class="text-sm text-slate-200">{{ item.arti }}</p>
                    <p class="text-xs text-slate-500 mt-2 line-clamp-2" v-if="item.deskripsi"><span class="font-semibold">Tafsir:</span> {{ item.deskripsi }}</p>
                  </td>
                  <td class="px-4 py-4 text-indigo-400 text-xs font-medium align-top">{{ item.keyword }}</td>
                  <td class="px-4 py-4 text-center align-top">
                    <a v-if="item.gambar_path" :href="'/storage/' + item.gambar_path" target="_blank" class="text-blue-400 hover:underline text-xs">Lihat File</a>
                    <span v-else class="text-slate-600 text-xs">-</span>
                  </td>
                </tr>
                <tr v-if="dalils.length === 0"><td colspan="5" class="text-center py-8 text-slate-500 italic">Belum ada dalil yang diinputkan.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ========================================== -->
      <!-- TAB 3: KELOLA GURU -->
      <!-- ========================================== -->
      <div v-if="activeTab === 'guru'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-fade-in">
        
        <!-- FORM GURU -->
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 p-6 rounded-3xl h-fit">
          <h2 class="text-xl font-bold mb-6">{{ isEditingGuru ? 'Edit Data Guru' : 'Daftarkan Guru Baru' }}</h2>
          <form @submit.prevent="submitGuru" class="space-y-4 text-sm">
            <div>
              <label class="block text-slate-400 mb-1 text-xs">Nama Lengkap</label>
              <input v-model="formGuru.name" type="text" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" required />
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">Email / Username</label>
              <input v-model="formGuru.email" type="email" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" required />
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">Password <span class="text-slate-600">{{ isEditingGuru ? '(Kosongkan jika tidak ubah)' : '(Minimal 8 Karakter)' }}</span></label>
              <input v-model="formGuru.password" type="password" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" :required="!isEditingGuru" />
            </div>
            <div>
              <label class="block text-slate-400 mb-1 text-xs">Jabatan</label>
              <input v-model="formGuru.jabatan" type="text" placeholder="Contoh: Guru Matematika" class="w-full bg-slate-950 border-slate-700 rounded-lg p-2.5" />
            </div>
            
            <div class="pt-2 flex gap-2">
              <button type="submit" :disabled="formGuru.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 py-3 rounded-xl font-bold transition disabled:opacity-50">
                {{ isEditingGuru ? 'Simpan Perubahan' : 'Daftarkan Akun' }}
              </button>
              <button v-if="isEditingGuru" @click="cancelEditGuru" type="button" class="px-4 bg-slate-800 hover:bg-slate-700 py-3 rounded-xl font-bold transition">
                Batal
              </button>
            </div>
          </form>
        </div>

        <!-- TABEL GURU -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-3xl overflow-hidden">
          <h2 class="text-xl font-bold mb-6">Daftar Akun Guru Aktif</h2>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="text-xs text-slate-500 uppercase bg-slate-950">
                <tr>
                  <th class="px-4 py-3 rounded-tl-lg">Nama Guru</th>
                  <th class="px-4 py-3">Email Akses</th>
                  <th class="px-4 py-3">Jabatan</th>
                  <th class="px-4 py-3 text-center rounded-tr-lg">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr v-for="guru in gurus" :key="guru.id" class="hover:bg-slate-800/50">
                  <td class="px-4 py-4 font-medium">{{ guru.name }}</td>
                  <td class="px-4 py-4 text-slate-400">{{ guru.email }}</td>
                  <td class="px-4 py-4"><span class="bg-indigo-500/10 text-indigo-400 px-2 py-1 rounded text-xs">{{ guru.jabatan || 'Guru' }}</span></td>
                  <td class="px-4 py-4 text-center">
                    <button @click="editGuru(guru)" class="text-emerald-400 hover:text-emerald-300 font-medium mr-3">Edit</button>
                    <button @click="deleteGuru(guru.id)" class="text-red-400 hover:text-red-300 font-medium">Hapus</button>
                  </td>
                </tr>
                <tr v-if="gurus.length === 0"><td colspan="4" class="text-center py-8 text-slate-500">Belum ada akun guru yang terdaftar.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

// 1. Props from Controller
const props = defineProps({
  fourCs: Array,
  dalils: Array,
  gurus: Array
});

// 2. Global State
const tabs = [
  { id: '4c', label: '🎯 Batasan 4C' },
  { id: 'dalil', label: '📖 Kelola Dalil' },
  { id: 'guru', label: '👨‍🏫 Kelola Guru' }
];
const activeTab = ref('dalil'); // Default tab for easy testing
const notification = ref({ show: false, message: '', isError: false });

const showNotification = (msg, isError = false) => {
  notification.value = { show: true, message: msg, isError };
  setTimeout(() => notification.value.show = false, 4000);
};

// ==========================================
// LOGIKA BATASAN 4C (CREATE, UPDATE, DELETE)
// ==========================================
const isEditing4C = ref(false);
const editing4CId = ref(null);
const form4C = useForm({ kategori: 'Creativity', batasan_deskripsi: '' });

const select4C = (cat) => { 
  form4C.kategori = cat; 
  cancelEdit4C(); // Reset edit state when changing category
};

const submit4C = () => {
  if (isEditing4C.value) {
    form4C.put(`/admin/4c/${editing4CId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        cancelEdit4C();
        showNotification('Batasan 4C berhasil diperbarui.');
      },
      onError: () => showNotification('Gagal memperbarui batasan.', true)
    });
  } else {
    form4C.post('/admin/4c', {
      preserveScroll: true,
      onSuccess: () => {
        form4C.reset('batasan_deskripsi');
        showNotification('Batasan 4C berhasil ditambahkan.');
      },
      onError: () => showNotification('Gagal menyimpan batasan.', true)
    });
  }
};

const edit4C = (item) => {
  isEditing4C.value = true;
  editing4CId.value = item.id;
  form4C.kategori = item.kategori;
  form4C.batasan_deskripsi = item.batasan_deskripsi;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelEdit4C = () => {
  isEditing4C.value = false;
  editing4CId.value = null;
  form4C.reset('batasan_deskripsi');
};

const delete4C = (id) => {
  if (confirm('PERINGATAN: Yakin ingin menghapus parameter batasan ini?')) {
    useForm({}).delete(`/admin/4c/${id}`, {
      preserveScroll: true,
      onSuccess: () => showNotification('Parameter 4C telah dihapus.'),
    });
  }
};


// ==========================================
// LOGIKA DALIL
// ==========================================
const formDalil = useForm({ 
  kategori: 'Al-Quran', 
  referensi: '', 
  arti: '', 
  deskripsi: '', 
  keyword: '', 
  gambar: null 
});

const handleImageUpload = (e) => {
  formDalil.gambar = e.target.files[0];
};

const submitDalil = () => {
  formDalil.post('/admin/dalil', {
    preserveScroll: true,
    onSuccess: () => {
      formDalil.reset();
      // Reset input type file manually
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = '';
      
      showNotification('Dalil berhasil ditambahkan.');
    },
    onError: () => showNotification('Gagal menambah dalil, periksa isian Anda.', true)
  });
};

// ==========================================
// LOGIKA KELOLA GURU (CRUD LENGKAP)
// ==========================================
const isEditingGuru = ref(false);
const editingGuruId = ref(null);

const formGuru = useForm({
  name: '',
  email: '',
  password: '',
  jabatan: ''
});

// CREATE / UPDATE GURU
const submitGuru = () => {
  if (isEditingGuru.value) {
    // Proses UPDATE
    formGuru.put(`/admin/guru/${editingGuruId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        cancelEditGuru();
        showNotification('Data guru berhasil diperbarui!');
      },
      onError: (errors) => {
        const errorMsg = Object.values(errors)[0];
        showNotification(errorMsg || 'Gagal mengupdate data.', true);
      }
    });
  } else {
    // Proses CREATE
    formGuru.post('/admin/guru', {
      preserveScroll: true,
      onSuccess: () => {
        formGuru.reset();
        showNotification('Akun guru berhasil didaftarkan!');
      },
      onError: (errors) => {
        const errorMsg = Object.values(errors)[0];
        showNotification(errorMsg || 'Gagal mendaftar, periksa form Anda.', true);
      }
    });
  }
};

// TOMBOL EDIT GURU
const editGuru = (guru) => {
  isEditingGuru.value = true;
  editingGuruId.value = guru.id;
  formGuru.name = guru.name;
  formGuru.email = guru.email;
  formGuru.jabatan = guru.jabatan;
  formGuru.password = ''; // Kosongkan agar aman
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// BATAL EDIT
const cancelEditGuru = () => {
  isEditingGuru.value = false;
  editingGuruId.value = null;
  formGuru.reset();
};

// HAPUS GURU
const deleteGuru = (id) => {
  if (confirm('PERINGATAN: Yakin ingin menghapus akses akun guru ini?')) {
    useForm({}).delete(`/admin/guru/${id}`, {
      preserveScroll: true,
      onSuccess: () => showNotification('Akun guru telah dihapus.'),
    });
  }
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
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>