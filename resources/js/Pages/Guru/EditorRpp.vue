<template>
  <div class="relative w-full max-w-5xl mx-auto mt-10 p-4">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Editor RPP AI (Interaktif)</h2>
      <div class="flex items-center gap-3">
        <a href="/guru" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-lg shadow-sm transition">Kembali ke Dashboard</a>
        <a href="/logout" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg shadow-sm transition">Keluar</a>
      </div>
    </div>
    
    <div v-if="notification.show" :class="['fixed bottom-5 right-5 px-6 py-3 rounded-xl shadow-lg text-white z-50 font-semibold transition-all duration-300', notification.isError ? 'bg-red-600' : 'bg-emerald-600']">
      {{ notification.message }}
    </div>

    <div 
      v-if="showFloatingMenu" 
      class="absolute bg-white shadow-2xl rounded-xl p-2 flex gap-2 border border-gray-200 z-50 transition-all duration-200"
      :style="{ top: menuPosition.y + 'px', left: menuPosition.x + 'px' }"
    >
      <button @click="handleGenerateImage" class="text-xs bg-indigo-600 text-white px-3 py-2 rounded-lg font-bold hover:bg-indigo-700 transition">
        <span v-if="loadingImg">Memproses Gambar...</span>
        <span v-else>📸 Generate Gambar AI</span>
      </button>
      <button @click="openAiEditSidebar" class="text-xs bg-purple-600 text-white px-3 py-2 rounded-lg font-bold hover:bg-purple-700 transition">
        🤖 Edit / Tanya AI
      </button>
    </div>

    <div id="editorjs" class="bg-white p-12 shadow-md border border-gray-200 rounded-3xl min-h-[600px] text-gray-800" @mouseup="handleTextSelection"></div>
    
    <div class="mt-6 flex justify-end">
      <button @click="saveAndDownload" class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-emerald-700 transition">
        Simpan & Unduh Berkas PDF
      </button>
    </div>

    <div v-if="isSidebarOpen" class="fixed right-0 top-0 w-96 h-full bg-white shadow-2xl p-6 border-l border-gray-200 z-50 flex flex-col">
      <div class="flex justify-between items-center mb-6">
        <h3 class="font-bold text-xl text-indigo-900">Asisten AI Groq</h3>
        <button @click="isSidebarOpen = false" class="text-red-500 font-extrabold hover:bg-red-50 p-2 rounded-lg transition">X</button>
      </div>
      
      <div class="overflow-y-auto flex-1 pr-2">
        <p class="text-xs font-semibold text-gray-400 mb-2 uppercase">Kutipan Kalimat Terpilih:</p>
        <p class="text-sm text-gray-600 italic bg-gray-50 p-3 rounded-lg border-l-4 border-purple-500 mb-6">"{{ selectedText }}"</p>
        
        <div class="mb-6">
          <label class="block text-xs font-bold text-gray-700 mb-2">Perintah Manual Anda (Prompt):</label>
          <textarea 
            v-model="customAiPrompt" 
            rows="3" 
            class="w-full text-sm border border-gray-300 rounded-lg p-3 focus:ring-purple-500 focus:border-purple-500 outline-none transition" 
            placeholder="Contoh: Ubah kalimat ini menjadi bahasa anak-anak yang ceria..."
          ></textarea>
          <button 
            @click="processAiEdit(customAiPrompt)" 
            :disabled="!customAiPrompt.trim()"
            class="mt-2 w-full bg-purple-600 disabled:bg-purple-300 hover:bg-purple-700 text-white font-bold py-2.5 rounded-lg text-xs transition shadow-sm"
          >
            🚀 Kirim Instruksi Manual
          </button>
        </div>

        <div class="flex flex-col gap-2 border-t border-gray-100 pt-4">
          <p class="text-xs font-bold text-gray-500 mb-1">Atau gunakan template cepat:</p>
          <button @click="processAiEdit('Perpanjang kalimat ini menjadi lebih detail, berbobot, dan mengarah pada keaktifan siswa')" class="bg-gray-50 border border-gray-200 p-3 rounded-xl text-xs font-bold text-left hover:bg-purple-50 hover:border-purple-300 transition text-gray-700">📈 Perpanjang Kalimat RPP</button>
          <button @click="processAiEdit('Parafrase kalimat ini agar lebih luwes, komunikatif, dan mudah dipahami dalam kurikulum')" class="bg-gray-50 border border-gray-200 p-3 rounded-xl text-xs font-bold text-left hover:bg-purple-50 hover:border-purple-300 transition text-gray-700">🔄 Parafrase Kalimat</button>
          <button @click="processAiEdit('Berikan instruksi langkah praktikum berkelompok yang kreatif berdasarkan teks ini')" class="bg-gray-50 border border-gray-200 p-3 rounded-xl text-xs font-bold text-left hover:bg-purple-50 hover:border-purple-300 transition text-gray-700">🔬 Buat Panduan Kegiatan Siswa</button>
        </div>
        
        <div v-if="aiResponseText" class="mt-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl mb-4">
          <p class="text-sm mb-2 font-bold text-emerald-800">Respons AI Groq:</p>
          <p class="text-sm text-gray-700 select-all leading-relaxed whitespace-pre-wrap">{{ aiResponseText }}</p>
          <p class="text-[10px] text-emerald-600 mt-3 font-semibold">*Silakan blok teks di atas lalu salin (copy) dan tempel (paste) ke dalam editor utama.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import ImageTool from '@editorjs/image';

const props = defineProps({ 
    rppId: Number, 
    initialData: Object 
});

const showFloatingMenu = ref(false);
const menuPosition = ref({ x: 0, y: 0 });
const selectedText = ref('');
const savedBlockIndex = ref(0);
const isSidebarOpen = ref(false);
const loadingImg = ref(false);
const aiResponseText = ref('');
const customAiPrompt = ref('');
let editor = null;

const notification = ref({ show: false, message: '', isError: false });
const showNotification = (message, isError = false) => {
  notification.value = { show: true, message, isError };
  setTimeout(() => {
    notification.value.show = false;
  }, 4000);
};

onMounted(() => {
  editor = new EditorJS({
    holder: 'editorjs',
    tools: { 
        header: Header, 
        list: List, 
        image: {
            class: ImageTool,
            config: {
                // SOLUSI PAMUNGKAS: Override sistem upload Editor.js!
                // Ini mencegah pesan error "Couldn't upload image" selamanya.
                uploader: {
                    uploadByUrl(url) {
                        return Promise.resolve({
                            success: 1,
                            file: { url: url }
                        });
                    },
                    uploadByFile(file) {
                        return Promise.resolve({
                            success: 1,
                            file: { url: URL.createObjectURL(file) }
                        });
                    }
                }
            }
        }
    },
    data: props.initialData 
  });
});

const handleTextSelection = () => {
  const selection = window.getSelection();
  const text = selection.toString().trim();
  if (text.length > 0) {
    const range = selection.getRangeAt(0).getBoundingClientRect();
    savedBlockIndex.value = editor.blocks.getCurrentBlockIndex();

    menuPosition.value = { 
        x: range.left + window.scrollX, 
        y: range.top + window.scrollY - 55 
    };
    selectedText.value = text;
    showFloatingMenu.value = true;
  } else {
    showFloatingMenu.value = false;
  }
};

const handleGenerateImage = async () => {
    loadingImg.value = true;
    try {
        const res = await axios.post('/rpp/generate-image', { prompt: selectedText.value });
        
        // Menyisipkan gambar tepat di Bawah baris teks yang diblok
        editor.blocks.insert('image', { 
            file: { url: res.data.image_url }, 
            caption: 'Ilustrasi AI: ' + selectedText.value,
            withBorder: false,
            withBackground: false,
            stretched: false
        }, {}, savedBlockIndex.value + 1, true);

        showNotification('Ilustrasi gambar berhasil ditambahkan!');
    } catch (e) { 
        showNotification('Gagal generate gambar. Periksa koneksi internet Anda.', true);
    }
    loadingImg.value = false;
    showFloatingMenu.value = false;
};

const openAiEditSidebar = () => {
    showFloatingMenu.value = false;
    isSidebarOpen.value = true;
    aiResponseText.value = '';
    customAiPrompt.value = '';
};

const processAiEdit = async (instruction) => {
    if (!instruction) return;
    
    aiResponseText.value = "Asisten AI Groq sedang mengetik respons...";
    try {
        const res = await axios.post('/rpp/edit-text', { 
            text: selectedText.value, 
            instruction 
        });
        aiResponseText.value = res.data.result;
    } catch (e) { 
        aiResponseText.value = "Maaf, koneksi ke asisten AI terganggu."; 
    }
};

const saveAndDownload = async () => {
    try {
        const outputData = await editor.save();
        const res = await axios.post('/rpp/save', { 
            rpp_id: props.rppId, 
            content_json: outputData 
        });
        showNotification('Data berhasil disimpan! Mengunduh dokumen PDF...');
        window.open(res.data.pdf_url, '_blank');
    } catch (error) { 
        console.error(error);
        showNotification('Gagal mengekspor file PDF.', true);
    }
};
</script>