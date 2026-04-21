// FIX C8: Removed dead legacy buildGuests() / buildMenu() functions.
// Those referenced #applyGuests, #guestCount, #guestsContainer which no longer
// exist in the current reservation.php (it uses inline JS with its own IDs).
// Only keeping the shared utility listeners below.

document.addEventListener('DOMContentLoaded', () => {

  // Phone input: enforce numeric-only and 11-digit max
  const phone = document.querySelector('input[name="phone"]');
  if (phone) {
    phone.addEventListener('input', () => {
      phone.value = phone.value.replace(/[^0-9]/g, '').slice(0, 11);
    });
  }

  // Time input: block times outside restaurant hours
  const time = document.querySelector('input[name="time"]');
  if (time) {
    time.addEventListener('change', () => {
      if (time.value < '11:00' || time.value > '23:00') {
        alert('Time must be between 11:00 and 23:00');
        time.value = '';
      }
    });
  }

});
