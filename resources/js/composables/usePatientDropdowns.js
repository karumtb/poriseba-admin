import { PATIENT_DROPDOWNS } from '@/constants/patientDropdowns';

export function usePatientDropdowns() {
  const getOptions = (key) => {
    return PATIENT_DROPDOWNS[key] || [];
  };

  const getLabel = (key, value) => {
    const option = getOptions(key).find((item) => item.value === value);

    return option?.label || '';
  };

  return {
    patientDropdowns: PATIENT_DROPDOWNS,
    getOptions,
    getLabel,
  };
}
