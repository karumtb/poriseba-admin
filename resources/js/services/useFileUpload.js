import { ref, computed } from 'vue'

export function useFileUpload() {
    const file = ref(null)
    const previewUrl = ref('')
    const fileError = ref('')

    const fileBasename = computed(() => {
        return file.value ? file.value.name : ''
    })

    const handleFileChange = (
        event,
        allowedTypes = [],
        maxSizeMB = 5
    ) => {
        const selectedFile = event.target.files?.[0]

        if (!selectedFile) {
            return
        }

        // File type validation
        if (
            allowedTypes.length &&
            !allowedTypes.includes(selectedFile.type)
        ) {
            file.value = null
            fileError.value = `Only ${allowedTypes.join(', ')} files are allowed.`

            event.target.value = ''
            return
        }

        // File size validation
        const maxSize = maxSizeMB * 1024 * 1024

        if (selectedFile.size > maxSize) {
            file.value = null
            fileError.value = `File size must be less than ${maxSizeMB} MB.`

            event.target.value = ''
            return
        }

        fileError.value = ''
        file.value = selectedFile

        // Remove old preview
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value)
        }

        // Preview only when possible
         previewUrl.value = URL.createObjectURL(selectedFile)
    }

    const clearFile = (inputRef = null) => {
         // Release browser object URL
        if (previewUrl.value?.startsWith('blob:')) {
            URL.revokeObjectURL(previewUrl.value)
        }

        // Clear Vue state
        file.value = null
        previewUrl.value = ''
        fileError.value = ''

        // Clear actual <input type="file">
        if (inputRef) {
            inputRef.value = ''
        }
    }

    return {
        file,
        fileBasename,
        previewUrl,
        fileError,
        handleFileChange,
        clearFile,
    }
}
