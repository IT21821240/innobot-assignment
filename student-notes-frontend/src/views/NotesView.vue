<script setup>
import { ref, onMounted } from 'vue'
import { getNotes, createNote, archiveNote } from '../services/noteService'
import { computed } from 'vue'

const allNotes = ref([])
const notes = computed(() => allNotes.value)
const currentPage = ref(1)
const perPage = 5
const loading = ref(false)
const error = ref('')
const validationErrors = ref({})
const creating = ref(false)
const archivingId = ref(null)
const toast = ref({
  show: false,
  message: '',
  type: 'success', // success | error
})

let toastTimeout = null

const showToast = (message, type = 'success') => {
  clearTimeout(toastTimeout)

  toast.value = { show: true, message, type }

  toastTimeout = setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

const filter = ref('')

const form = ref({
  title: '',
  content: '',
  priority: 'low',
})

const fetchNotes = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await getNotes(filter.value)
    allNotes.value = response.data
    currentPage.value = 1
  } catch {
    error.value = 'Failed to load notes'
  } finally {
    loading.value = false
  }
}

const paginatedNotes = computed(() => {
  const start = (currentPage.value - 1) * perPage
  const end = start + perPage
  return allNotes.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(allNotes.value.length / perPage)
})

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const handleCreate = async () => {
  validationErrors.value = {}
  error.value = ''
  creating.value = true

  if (!form.value.title.trim()) {
    validationErrors.value.title = 'Title is required'
  }

  if (!form.value.content.trim()) {
    validationErrors.value.content = 'Content is required'
  }

  if (form.value.title.length > 120) {
    validationErrors.value.title = 'Title must not exceed 120 characters'
  }

  if (form.value.content.length > 1000) {
    validationErrors.value.content = 'Content must not exceed 1000 characters'
  }

  if (Object.keys(validationErrors.value).length) {
  showToast('Please fix validation errors', 'error')
    return
  }

  try {
    await createNote(form.value)

    showToast('Note created successfully.', 'success')

    form.value = {
      title: '',
      content: '',
      priority: 'low',
    }

    await fetchNotes()
  } catch (err) {
    if (err.response?.status === 422) {
      validationErrors.value = err.response.data.errors
    } else {
      showToast('Failed to create note', 'error')
    }
  } finally {
    creating.value = false
  }
}

const handleArchive = async (id) => {
archivingId.value = id
  try {
    await archiveNote(id)
    showToast('Note archived successfully.', 'success')
    await fetchNotes()
  } catch {
    showToast('Failed to archive note', 'error')
  }finally {
    archivingId.value = null
  }
}

onMounted(fetchNotes)
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 px-4 py-10">
    <div class="max-w-5xl mx-auto px-4">
      <!-- Header -->
      <div class="text-center mb-10">
        <h1 class="text-5xl font-bold text-slate-900 mb-3">Student Notes</h1>

        <p class="text-slate-600 max-w-2xl mx-auto">
          A simple note management application where students can create, organize, filter, and
          archive notes based on priority levels. Designed to help keep important tasks and study
          reminders organized.
        </p>
      </div>

      <!-- Create Note -->
      <div class="bg-white rounded-2xl shadow-md p-6 mb-8">
        <h2 class="text-2xl font-semibold mb-6">Create New Note</h2>

        <div class="space-y-5">
          <!-- Title -->
          <div>
            <label class="block mb-2 font-medium text-slate-700"> Title </label>

            <input
              v-model="form.title"
              placeholder="Enter note title"
              class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />

            <div class="flex justify-between mt-1">
              <p v-if="validationErrors.title" class="text-red-500 text-sm">
                {{
                  Array.isArray(validationErrors.title)
                    ? validationErrors.title[0]
                    : validationErrors.title
                }}
              </p>

              <span class="text-xs text-slate-500 ml-auto"> {{ form.title.length }}/120 </span>
            </div>
          </div>

          <!-- Content -->
          <div>
            <label class="block mb-2 font-medium text-slate-700"> Content </label>

            <textarea
              v-model="form.content"
              rows="5"
              placeholder="Write your note here..."
              class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            ></textarea>

            <div class="flex justify-between mt-1">
              <p v-if="validationErrors.content" class="text-red-500 text-sm">
                {{
                  Array.isArray(validationErrors.content)
                    ? validationErrors.content[0]
                    : validationErrors.content
                }}
              </p>

              <span class="text-xs text-slate-500 ml-auto"> {{ form.content.length }}/1000 </span>
            </div>
          </div>

          <!-- Priority -->
          <div>
            <label class="block mb-2 font-medium text-slate-700"> Priority </label>

            <select v-model="form.priority" class="w-full border rounded-lg p-3">
              <option value="low">Low Priority</option>
              <option value="medium">Medium Priority</option>
              <option value="high">High Priority</option>
            </select>
          </div>

          <button
            @click="handleCreate"
            :disabled="creating.value"
            class="bg-slate-700 text-white px-6 py-3 rounded-lg hover:bg-slate-800 transition"
          >
            Create Note
          </button>
        </div>
      </div>

      <!-- Filter -->
      <div
        class="bg-white rounded-2xl shadow-md p-5 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
      >
        <div>
          <h3 class="font-semibold text-lg">Notes List</h3>

          <p class="text-slate-500 text-sm">Filter notes by priority level.</p>
        </div>

        <select v-model="filter" @change="fetchNotes" class="border rounded-lg px-4 py-2">
          <option value="">All Notes</option>
          <option value="low">Low Priority</option>
          <option value="medium">Medium Priority</option>
          <option value="high">High Priority</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-xl shadow p-4 text-center">Loading notes...</div>

      <!-- API Error -->
      <div v-if="error" class="bg-red-100 border border-red-200 text-red-700 rounded-xl p-4 mb-4">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div
        v-if="!loading && notes.length === 0"
        class="bg-white rounded-xl shadow p-10 text-center"
      >
        <h3 class="text-xl font-semibold text-slate-700">No Notes Found</h3>

        <p class="text-slate-500 mt-2">Create your first note to get started.</p>
      </div>

      <!-- Toast -->
      <div
        v-if="toast.show"
        class="fixed top-5 right-5 px-5 py-3 rounded-lg shadow-lg text-white transition"
        :class="{
          'bg-green-600': toast.type === 'success',
          'bg-red-600': toast.type === 'error',
        }"
      >
        {{ toast.message }}
      </div>

      <!-- Notes -->
      <div class="grid gap-5">
        <div v-for="note in paginatedNotes" :key="note.id" class="bg-white rounded-2xl shadow-md p-6">
          <div class="flex justify-between items-start gap-4">
            <div>
              <h3 class="text-xl font-semibold text-slate-800">
                {{ note.title }}
              </h3>

              <p class="text-slate-600 mt-2">
                {{ note.content }}
              </p>
            </div>

            <span
              class="px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap"
              :class="{
                'bg-green-100 text-green-700': note.priority === 'low',
                'bg-yellow-100 text-yellow-700': note.priority === 'medium',
                'bg-red-100 text-red-700': note.priority === 'high',
              }"
            >
              {{ note.priority }}
            </span>
          </div>

          <div class="mt-5 flex justify-between items-center">
            <span
              v-if="note.archived"
              class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-sm"
            >
              Archived
            </span>

            <button
              v-else
              @click="handleArchive(note.id)"
              :disabled="archivingId === note.id"
              class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-900 transition"
            >
              Archive Note
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-if="totalPages > 1" class="flex justify-center items-center gap-3 mt-6">
  <button
    class="px-3 py-1 border rounded"
    :disabled="currentPage === 1"
    @click="prevPage"
  >
    Prev
  </button>

  <span class="text-sm text-gray-600">
    Page {{ currentPage }} of {{ totalPages }}
  </span>

  <button
    class="px-3 py-1 border rounded"
    :disabled="currentPage === totalPages"
    @click="nextPage"
  >
    Next
  </button>
</div>
</template>
