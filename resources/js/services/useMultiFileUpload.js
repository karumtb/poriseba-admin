import { ref, computed, reactive } from 'vue'
import api from '@/services/axios'

export function useFileUpload() {

    const files = reactive([])

    const uploadedImages = computed(() =>
        files
            .filter(item => item.uploaded)
            .map(item => item.response?.image)
    )

    const hasImages = computed(() => uploadedImages.value.length > 0)

    const handleMultipleFileChange = async (
        event,
        uploadUrl = '/image/upload',
        allowedTypes = []
    ) => {

        const selectedFiles = Array.from(event.target.files)

        for (const selectedFile of selectedFiles) {

            if (allowedTypes.length && !allowedTypes.includes(selectedFile.type)) {
                continue
            }

            // ✅ reactive() instead of a plain object
            const item = reactive({
                id: Date.now() + Math.random(),
                file: selectedFile,
                preview: URL.createObjectURL(selectedFile),
                progress: 0,
                uploading: true,
                uploaded: false,
                error: '',
                response: null
            })

            files.push(item)

            await uploadFile(item, uploadUrl)
        }

        event.target.value = ''
    }

    const uploadFile = async (item, uploadUrl) => {
        const formData = new FormData()
        formData.append('image', item.file)

        try {
            const response = await api.post(uploadUrl, formData, {
                skipLoading: true,
                onUploadProgress(progressEvent) {
                    if (progressEvent.total) {
                        item.progress = Math.round(
                            (progressEvent.loaded * 100) / progressEvent.total
                        )
                    }
                }
            })

            if (response.data.status) {
                item.uploaded = true
                item.uploading = false
                item.response = response.data
            }

        } catch (e) {
            item.uploading = false
            item.error = 'Upload Failed'
        }
    }

    const removeFile = async (index) => {
        const file = files[index]

        try {
            if (file.uploaded) {
                await api.post('/image/delete', {
                    image: file.response?.image  // see note below
                })
            }
        } catch (e) {
            console.log(e)
        }

        URL.revokeObjectURL(file.preview)
        files.splice(index, 1)
    }

    return { files, uploadedImages, hasImages, handleMultipleFileChange, removeFile }
}
