import { mount, flushPromises } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import NoteApp from '../src/views/NotesView.vue'
import { createNote } from '../src/services/noteService'

vi.mock('../src/services/noteService', () => ({
  getNotes: vi.fn(() => Promise.resolve({ data: [] })),
  createNote: vi.fn(() => Promise.resolve({})),
  archiveNote: vi.fn(() => Promise.resolve({}))
}))

describe('NotesView.vue', () => {

  it('shows validation error when title is empty', async () => {
    const wrapper = mount(NoteApp)

    await wrapper.find('button').trigger('click')

    expect(wrapper.text()).toContain('Title is required')
  })

  it('creates note successfully', async () => {
    const wrapper = mount(NoteApp)

    await wrapper.find('input').setValue('Test Note')
    await wrapper.find('textarea').setValue('Test Content')

    await wrapper.find('button').trigger('click')

    await flushPromises()

    // check API call
    expect(createNote).toHaveBeenCalled()

    // check success message
    expect(wrapper.text()).toContain('Note created successfully.')
  })
})