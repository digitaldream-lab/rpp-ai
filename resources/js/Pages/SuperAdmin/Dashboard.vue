<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 border-b border-slate-800 pb-6">
        <div>
          <span class="text-indigo-400 font-extrabold uppercase tracking-wider text-xs">Konsol Kontrol Utama</span>
          <h1 class="text-4xl font-extrabold tracking-tight text-white mt-1">Superadmin Console</h1>
          <p class="text-sm text-slate-400 mt-1">Kendalikan batasan kurikulum 4C dan pantau database keaslian dalil agama.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4">
          <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">Akses Penuh</span>
          <a href="/logout" class="bg-red-600 hover:bg-red-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-md transition">Logout</a>
        </div>
      </div>

      <div v-if="notification.show" :class="['fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-lg text-white z-50 font-semibold transition-all duration-300', notification.isError ? 'bg-red-600' : 'bg-indigo-600']">
        {{ notification.message }}
      </div>

      <div class="flex space-x-1 bg-slate-900 border border-slate-800 p-1.5 rounded-xl mb-10 max-w-lg">
        <button v-for="tab in ['batasan_4c', 'kelola_dalil', 'kelola_guru']" :key="tab" @click="activeTab = tab"
          :class="['w-full py-2 text-sm font-semibold rounded-lg transition-all', activeTab === tab ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800']">
          {{ tab === 'batasan_4c' ? '🎯 Batasan 4C' : (tab === 'kelola_dalil' ? '📖 Kelola Dalil' : '👨‍🏫 Kelola Guru') }}
        </button>
      </div>

      <div v-if="activeTab === 'batasan_4c'" class="space-y-10">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div 
            v-for="cat in ['Creativity', 'Critical Thinking', 'Communication', 'Collaboration']" 
            :key="cat"
            @click="select4C(cat)"
            :class="[
              'p-6 rounded-2xl border transition cursor-pointer flex flex-col justify-between h-44',
              selectedCategory === cat ? 'bg-indigo-950/40 border-indigo-500 shadow-lg shadow-indigo-500/10' : 'bg-slate-900/60 border-slate-800 hover:border-slate-700'
            ]"
          >
            <div>
              <h3 class="font-extrabold text-lg text-white">{{ cat }}</h3>
              <p class="text-xs text-slate-400 mt-1">Klik untuk membuat batasan parameter input AI.</p>
            </div>
            <span class="text-xs text-indigo-400 font-bold">
              {{ countLimits(cat) }} Parameter Aktif &rarr;
            </span>
          </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-8 rounded-3xl max-w-2xl mx-auto">
          <h3 class="text-xl font-bold text-white mb-2">B1. Buat Batasan Baru: <span class="text-indigo-400">{{ selectedCategory }}</span></h3>
          <p class="text-xs text-slate-400 mb-6">Tulis batasan spesifik. Parameter ini akan membatasi pemikiran AI agar hasil generate RPP sesuai SOP sekolah.</p>

          <form @submit.prevent="submit4C" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-slate-300 mb-1">Definisi Batasan Kurikulum</label>
              <textarea 
                v-model="form4C.batasan_deskripsi" 
                rows="4"
                placeholder="Tulis instruksi rinci bagaimana AI harus merumuskan metode ini..."
                class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                required
              ></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow transition">
              Simpan & Terapkan Parameter
            </button>
          </form>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-800">
            <h4 class="font-bold text-white">Daftar Parameter Batasan AI Aktif</h4>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
              <thead class="bg-slate-950 text-slate-400 font-semibold text-xs">
                <tr>
                  <th class="px-6 py-3 text-left uppercase">Kategori 4C</th>
                  <th class="px-6 py-3 text-left uppercase">Batasan Deskripsi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr v-for="item in fourCs" :key="item.id" class="hover:bg-slate-900/30">
                  <td class="px-6 py-4 whitespace-nowrap font-bold text-indigo-400">{{ item.kategori }}</td>
                  <td class="px-6 py-4 text-slate-300">{{ item.batasan_deskripsi }}</td>
                </tr>
                <tr v-if="fourCs.length === 0">
                  <td colspan="2" class="px-6 py-8 text-center text-slate-500 italic">Belum ada batasan 4C yang dibuat.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <div v-if="activeTab === 'kelola_dalil'" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 p-6 rounded-3xl h-fit">
          <h2 class="text-xl font-bold text-white mb-2">B2. Daftarkan Dalil</h2>
          <p class="text-xs text-slate-400 mb-6">Batasan ketat AI materi Agama: AI dilarang menulis dalil di luar database ini.</p>

          <form @submit.prevent="submitDalil" class="space-y-4 text-sm">
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Kategori Dalil</label>
              <select v-model="formDalil.kategori" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" required>
                <option value="Al-Quran">Al-Quran</option>
                <option value="Hadis">Hadis</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Referensi / Sumber Kitab</label>
              <input v-model="formDalil.referensi" type="text" placeholder="Contoh: QS. Al-Baqarah: 151 atau Bukhari No. 12" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" required />
            </div>
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Arti Terjemahan</label>
              <textarea v-model="formDalil.arti" rows="3" placeholder="Arti dalil dalam bahasa Indonesia..." class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" required></textarea>
            </div>
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Deskripsi & Tafsir Konteks</label>
              <textarea v-model="formDalil.deskripsi" rows="2" placeholder="Tafsir atau penjelasan singkat..." class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100"></textarea>
            </div>
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Kata Kunci Pencarian (Keywords)</label>
              <input v-model="formDalil.keyword" type="text" placeholder="Contoh: air, bersih, wudhu, bumi" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" required />
            </div>
            <div>
              <label class="block font-semibold text-slate-300 mb-1">Unggah Gambar Scan Kitab (Opsional)</label>
              <input type="file" @change="handleImageUpload" accept="image/*" class="w-full border border-slate-800 bg-slate-950 p-2 rounded-lg text-slate-400" />
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow transition">
              Simpan & Daftarkan Dalil
            </button>
          </form>
        </div>

        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-3xl">
          <h2 class="text-xl font-bold text-white mb-6">Database Dalil Agama Terverifikasi</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
              <thead class="bg-slate-950 text-slate-400 font-semibold text-xs">
                <tr>
                  <th class="px-6 py-3 text-left uppercase">Kategori</th>
                  <th class="px-6 py-3 text-left uppercase">Referensi</th>
                  <th class="px-6 py-3 text-left uppercase">Arti Terjemahan</th>
                  <th class="px-6 py-3 text-left uppercase">Kata Kunci</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800 text-slate-300">
                <tr v-for="item in dalils" :key="item.id" class="hover:bg-slate-900/30">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="[
                        'px-2.5 py-1 rounded text-xs font-bold',
                        item.kategori === 'Al-Quran' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                      ]"
                    >
                      {{ item.kategori }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-bold text-white">{{ item.referensi }}</td>
                  <td class="px-6 py-4 text-xs max-w-sm truncate">{{ item.arti }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-indigo-400 text-xs font-semibold">{{ item.keyword }}</td>
                </tr>
                <tr v-if="dalils.length === 0">
                  <td colspan="4" class="px-6 py-10 text-center text-slate-500 italic">Database dalil kosong. Silakan tambahkan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <div v-if="activeTab === 'kelola_guru'" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 p-6 rounded-3xl h-fit">
          <h2 class="text-xl font-bold text-white mb-6">{{ editingGuruId ? 'Edit Guru' : 'Daftarkan Guru' }}</h2>
          <form @submit.prevent="editingGuruId ? saveEditGuru(editingGuruId) : submitGuru" class="space-y-4 text-sm">
            <input v-model="formGuru.name" placeholder="Nama Lengkap" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" required />
            <input v-model="formGuru.email" type="email" placeholder="Email/Username" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" required />
            <input v-model="formGuru.password" type="password" :placeholder="editingGuruId ? 'Password baru (opsional)' : 'Password'" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" />
            <input v-model="formGuru.jabatan" placeholder="Jabatan" class="w-full bg-slate-950 border-slate-800 rounded-lg text-slate-100" />
            <button class="w-full bg-indigo-600 py-3 rounded-xl font-bold text-white">{{ editingGuruId ? 'Simpan Perubahan' : 'Daftarkan Guru' }}</button>
            <button v-if="editingGuruId" @click="resetGuruForm" type="button" class="w-full text-slate-400 text-xs">Batal Edit</button>
          </form>
        </div>
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-3xl">
          <table class="w-full text-sm divide-y divide-slate-800 text-slate-300">
            <thead class="text-slate-400 text-xs uppercase text-left"><tr><th class="py-3">Nama</th><th>Email</th><th>Jabatan</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
              <tr v-for="guru in gurus" :key="guru.id" class="border-t border-slate-800">
                <td class="py-4">{{ guru.name }}</td><td>{{ guru.email }}</td><td>{{ guru.jabatan }}</td>
                <td class="text-center"><button @click="startEditGuru(guru)" class="text-blue-400 mr-2">Edit</button><button @click="deleteGuru(guru.id)" class="text-red-400">Hapus</button></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>



    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ fourCs: Array, dalils: Array, gurus: Array });
const activeTab = ref('batasan_4c');
const selectedCategory = ref('Creativity');

// Notifikasi Kustom menggantikan alert()
const notification = ref({ show: false, message: '', isError: false });
const showNotification = (message, isError = false) => {
  notification.value = { show: true, message, isError };
  setTimeout(() => {
    notification.value.show = false;
  }, 4000);
};

const form4C = useForm({
  kategori: 'Creativity',
  batasan_deskripsi: ''
});

const formDalil = useForm({
  kategori: 'Al-Quran',
  referensi: '',
  arti: '',
  deskripsi: '',
  keyword: '',
  gambar: null
});

const formGuru = useForm({ name: '', email: '', password: '', jabatan: '' });

const select4C = (category) => {
  selectedCategory.value = category;
  form4C.kategori = category;
};

const countLimits = (category) => {
  return props.fourCs.filter(item => item.kategori === category).length;
};

const handleImageUpload = (e) => {
  formDalil.gambar = e.target.files[0];
};

const submit4C = () => {
  form4C.post(route('admin.4c.store'), {
    onSuccess: () => {
      form4C.reset('batasan_deskripsi');
      showNotification('Parameter batasan kurikulum 4C berhasil disimpan.');
    }
  });
};

const submitDalil = () => {
  formDalil.post(route('admin.dalil.store'), {
    onSuccess: () => {
      formDalil.reset();
      showNotification('Dalil agama baru berhasil diverifikasi dan ditambahkan!');
    }
  });

  const submitGuru = () => formGuru.post(route('admin.guru.store'), { onSuccess: () => { formGuru.reset(); showNotification('Guru ditambah.'); } });

  const startEditGuru = (guru) => { editingGuruId.value = guru.id; formGuru.name = guru.name; formGuru.email = guru.email; formGuru.jabatan = guru.jabatan; };
const saveEditGuru = (id) => formGuru.put(route('admin.guru.update', id), { onSuccess: () => { editingGuruId.value = null; formGuru.reset(); showNotification('Data diperbarui.'); } });
const deleteGuru = (id) => { if(confirm('Hapus guru?')) useForm({}).delete(route('admin.guru.destroy', id), { onSuccess: () => showNotification('Guru dihapus.'), }); };
const resetGuruForm = () => { editingGuruId.value = null; formGuru.reset(); };
};
</script>