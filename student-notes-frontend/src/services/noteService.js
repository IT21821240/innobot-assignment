import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api'
})

export const getNotes = (priority = '') =>
  api.get('/notes', {
    params: { priority }
  })

export const createNote = (data) =>
  api.post('/notes', data)

export const archiveNote = (id) =>
  api.patch(`/notes/${id}/archive`)