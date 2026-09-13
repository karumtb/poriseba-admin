import { reactive, computed } from 'vue'

export function useFormBuilder(schema) {

    const form = reactive({})
    const errors = reactive({})

    const emptyError = () => ({ type: null, message: '' })

    const resolveMessage = (field, type, fallback) => {
        // Priority: field.messages[type] -> field.message -> fallback
        return field.messages?.[type] || field.message || fallback
    }

    // Create form controls
    Object.keys(schema).forEach((key) => {
        form[key] = schema[key].default ?? ''
        errors[key] = emptyError()
    })

    const setError = (name, type, message) => {
        errors[name] = { type, message }
        return false
    }

    const clearError = (name) => {
        errors[name] = emptyError()
    }

    const validateField = (name) => {

        const field = schema[name]
        const value = form[name]
        clearError(name)
        const isRequired =typeof field.required === 'function'? field.required(): field.required

        if (isRequired) {
            const isEmpty =
                value === null ||
                value === undefined ||
                value === '' ||
                (typeof value === 'string' && !value.trim())

            if (isEmpty) {
                return setError(
                    name,
                    'required',
                    resolveMessage(field, 'required', `${name} is required`)
                )
            }
        }

        if (field.email && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

            if (!emailRegex.test(value)) {
                return setError(
                    name,
                    'email',
                    resolveMessage(field, 'email', 'Please enter a valid email address')
                )
            }
        }

        if (field.minLength && value) {
            if (value.length < field.minLength) {
                return setError(
                    name,
                    'minLength',
                    resolveMessage(field, 'minLength', `Minimum ${field.minLength} characters required`)
                )
            }
        }

        if (field.maxLength && value) {
            if (value.length > field.maxLength) {
                return setError(
                    name,
                    'maxLength',
                    resolveMessage(field, 'maxLength', `Maximum ${field.maxLength} characters allowed`)
                )
            }
        }

        // pattern checked after length so a "too short" case doesn't
        // also get reported as "invalid format"
        if (field.pattern && value) {
            if (!field.pattern.test(value)) {
                return setError(
                    name,
                    'pattern',
                    resolveMessage(field, 'pattern', `${name} is invalid`)
                )
            }
        }

        return true
    }

    const validate = () => {

        let valid = true

        Object.keys(schema).forEach((name) => {
            const result = validateField(name)
            if (!result)
            {
                valid = false
            }
        })

        return valid
    }

    const isInvalid = (name) => {
        return !!errors[name]?.message
    }

    const getError = (name) => {
        return errors[name]?.message || ''
    }

    const getErrorType = (name) => {
        return errors[name]?.type || null
    }

    const hasErrorType = (name, type) => {
        return errors[name]?.type === type
    }

    const reset = () => {
        Object.keys(schema).forEach((key) => {
            form[key] = schema[key].default ?? ''
            errors[key] = emptyError()
        })
    }

    const patchValue = (data) => {
        Object.keys(data).forEach((key) => {
            if (key in form) {
                form[key] = data[key]
            }
        })
    }

    const setValue = (name, value) => {
        if (name in form) {
            form[name] = value
        }
    }

    const getValue = (name) => {
        return form[name]
    }

    const value = computed(() => ({ ...form }))

    return {
        form,
        errors,
        value,
        validate,
        validateField,
        isInvalid,
        getError,
        getErrorType,
        hasErrorType,
        reset,
        patchValue,
        setValue,
        getValue,
    }
}
