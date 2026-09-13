export function allowDecimal(event, decimals = 2) {
    let value = event.target.value

    // Only numbers and decimal point
    value = value.replace(/[^0-9.]/g, '')

    // Only one decimal point
    const parts = value.split('.')

    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('')
    }

    // Limit decimal places
    if (parts[1]?.length > decimals) {
        value = parts[0] + '.' + parts[1].slice(0, decimals)
    }

    event.target.value = value

    return value
}
export function getFileName(path){
  if (!path) return '';
  return path.substring(path.lastIndexOf('/') + 1);
};

export function openFile(url){
  if (!url) return false;
  // '_blank' opens the URL in a new tab
  // 'noopener,noreferrer' is a security best practice
  window.open(url, '_blank', 'noopener,noreferrer');
};

