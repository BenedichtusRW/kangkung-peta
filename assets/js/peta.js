/**
 * assets/js/peta.js
 * Logika Peta Interaktif Khusus Area Kelurahan Kangkung.
 * Dibatasi ketat menggunakan Bounding Box (BBOX) Overpass API dengan Gambar Variatif per Kategori.
 */
(function () {
  const CFG = window.PETA_CONFIG || { lat: -5.4496, lng: 105.2685, zoom: 16 };

  let map, markersLayer;
  let allPlaces = [];
  let markerById = {};
  let activeKategori = 'semua';
  let searchQuery = '';
  let sortMode = 'default';
  let onlyOpenNow = false;
  let userLatLng = null;
  let userMarker = null;

  const KATEGORI_ICON = {
    tugu: 'fa-solid fa-monument',
    pemerintahan: 'fa-solid fa-building-columns',
    sekolah: 'fa-solid fa-graduation-cap',
    ibadah: 'fa-solid fa-mosque',
    kesehatan: 'fa-solid fa-hospital',
    kuliner: 'fa-solid fa-utensils',
    jasa: 'fa-solid fa-briefcase'
  };

  window.openDetailById = function(id) {
    const p = allPlaces.find(x => x.id == id);
    if (p) {
      openDetail(p);
    }
  };

  // ---------- Init map ----------
  function initMap() {
    map = L.map('map', { scrollWheelZoom: true }).setView([CFG.lat, CFG.lng], CFG.zoom);

    // Citra Satelit Google (Murni, tanpa POI & Label agar tidak bingung)
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
      attribution: '&copy; Google Maps',
      maxZoom: 20,
    }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    // Marker Pusat Kangkung
    L.circleMarker([CFG.lat, CFG.lng], {
      radius: 7,
      color: '#ffffff',
      fillColor: '#D6A24C',
      fillOpacity: 1,
      weight: 2,
    }).addTo(map).bindTooltip('Pusat Kelurahan Kangkung');
  }

  // ---------- Fetch Data API ----------
  function fetchPlaces() {
    const listEl = document.getElementById('placeListItems');
    if (listEl) listEl.innerHTML = '<p class="loading-text">Memuat data lokasi Kangkung...</p>';

    fetch(CFG.apiUrl)
      .then((res) => res.json())
      .then((resData) => {
        if (!resData.success) throw new Error(resData.message || 'Gagal memuat API');
        allPlaces = resData.data || [];
        renderAll();
      })
      .catch((err) => {
        console.error('API Error:', err);
        if (listEl) listEl.innerHTML = `<p class="empty-text">Gagal memuat lokasi dari peta.</p>`;
      });
  }

  // ---------- Helpers ----------
  function distanceMeters(a, b) {
    const R = 6371000;
    const dLat = ((b.lat - a.lat) * Math.PI) / 180;
    const dLng = ((b.lng - a.lng) * Math.PI) / 180;
    const lat1 = (a.lat * Math.PI) / 180;
    const lat2 = (b.lat * Math.PI) / 180;
    const h = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLng / 2) ** 2;
    return 2 * R * Math.asin(Math.sqrt(h));
  }

  function formatDistance(m) {
    if (m < 1000) return Math.round(m) + ' m';
    return (m / 1000).toFixed(1) + ' km';
  }

  function formatTravelTime(m) {
    const minutes = Math.ceil(m / 400); // Approx 24 km/h
    if (minutes <= 1) return '± 1 mnt';
    if (minutes >= 60) {
      const h = Math.floor(minutes / 60);
      const min = minutes % 60;
      return `± ${h} j ${min} m`.replace(' 0 m', '');
    }
    return `± ${minutes} mnt`;
  }

  function isOpenNow(jamBuka) {
    if (!jamBuka) return false;
    if (/24\s*jam/i.test(jamBuka)) return true;
    const match = jamBuka.match(/(\d{1,2})[:.](\d{2})\s*-\s*(\d{1,2})[:.](\d{2})/);
    if (!match) return false;
    const now = new Date();
    const nowMinutes = now.getHours() * 60 + now.getMinutes();
    const start = parseInt(match[1], 10) * 60 + parseInt(match[2], 10);
    const end = parseInt(match[3], 10) * 60 + parseInt(match[4], 10);
    return nowMinutes >= start && nowMinutes <= end;
  }

  function getFiltered() {
    let items = allPlaces.slice();

    if (activeKategori !== 'semua') {
      items = items.filter((p) => p.kategori === activeKategori);
    }
    if (searchQuery.trim() !== '') {
      const q = searchQuery.toLowerCase();
      items = items.filter(
        (p) =>
          p.nama.toLowerCase().includes(q) ||
          p.alamat.toLowerCase().includes(q) ||
          (p.deskripsi || '').toLowerCase().includes(q)
      );
    }
    if (onlyOpenNow) {
      items = items.filter((p) => isOpenNow(p.jam_buka));
    }
    if (userLatLng) {
      items.forEach((p) => {
        p._dist = distanceMeters(userLatLng, { lat: p.lat, lng: p.lng });
      });
    }

    if (sortMode === 'terdekat' && userLatLng) {
      items.sort((a, b) => (a._dist || 0) - (b._dist || 0));
    } else if (sortMode === 'az') {
      items.sort((a, b) => a.nama.localeCompare(b.nama));
    }

    return items;
  }

  // ---------- Render ----------
  function renderAll() {
    const items = getFiltered();
    renderMarkers(items);
    renderList(items);
    const countEl = document.getElementById('placeCount');
    if (countEl) countEl.textContent = `${items.length} Tempat Ditemukan`;
  }

  function renderMarkers(items) {
    markersLayer.clearLayers();
    markerById = {};

    items.forEach((p) => {
      const color = (CFG.kategoriWarna && CFG.kategoriWarna[p.kategori]) || '#E11D48';
      const iconChar = KATEGORI_ICON[p.kategori] || 'fa-solid fa-location-dot';

      const icon = L.divIcon({
        className: '',
        html: `<div style="
          width:32px;height:32px;border-radius:50% 50% 50% 0;
          background:${color};transform:rotate(-45deg);
          border:2px solid #ffffff;box-shadow:0 3px 8px rgba(0,0,0,.4);
          display:flex;align-items:center;justify-content:center;
        ">
          <i class="${iconChar}" style="transform:rotate(45deg);font-size:14px;color:#fff;"></i>
        </div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
      });

      let imgSrc = '';
      if (p.gambar) {
        imgSrc = p.gambar.startsWith('http') ? p.gambar : `../../${p.gambar}`;
      } else {
        imgSrc = `../../assets/img/placeholder-${p.kategori || 'default'}.jpg`;
      }
      const distInfo = p._dist !== undefined ? `<div style="font-size:11px; color:#10b981; font-weight:700; margin-bottom:8px;"><i class="fa-solid fa-route"></i> ${formatDistance(p._dist)} <span style="margin-left:6px;"><i class="fa-solid fa-car-side"></i> <i class="fa-solid fa-motorcycle"></i></span> ${formatTravelTime(p._dist)}</div>` : '';

      const popupHtml = `
        <div style="text-align:center; min-width:200px;">
          <h4 style="margin:0 0 4px; font-size:15px; color:#1f2937;">${p.nama}</h4>
          <span style="display:inline-block; padding:3px 8px; border-radius:12px; background:#f3f4f6; color:#0f766e; font-size:11px; font-weight:700; margin-bottom:8px;">${p.kategori.toUpperCase()}</span>
          ${distInfo}
          <p style="font-size:12px; color:#4b5563; margin:0 0 12px;">${p.deskripsi.substring(0, 50)}...</p>
          <button class="btn btn-primary" style="width:100%; padding:8px; font-size:12px; border-radius:6px; cursor:pointer;" onclick="window.openDetailById('${p.id}')">
            Lihat Detail
          </button>
        </div>
      `;

      const marker = L.marker([p.lat, p.lng], { icon }).addTo(markersLayer);
      marker.bindPopup(popupHtml);
      markerById[p.id] = marker;
    });
  }

  function renderList(items) {
    const listEl = document.getElementById('placeListItems');
    if (!listEl) return;

    if (items.length === 0) {
      listEl.innerHTML = '<p class="empty-text">Tidak ada tempat di Kangkung yang cocok.</p>';
      return;
    }

    listEl.innerHTML = items
      .map((p) => {
        let imgSrc = `../../assets/img/placeholder-${p.kategori || 'default'}.jpg`;
        if (p.gambar) imgSrc = p.gambar.startsWith('http') ? p.gambar : `../../${p.gambar}`;
        const distText = p._dist !== undefined ? `<span class="dist">${formatDistance(p._dist)} &bull; <i class="fa-solid fa-car-side"></i> <i class="fa-solid fa-motorcycle"></i> ${formatTravelTime(p._dist)}</span>` : '';
        return `
          <div class="place-item" data-id="${p.id}" style="display:flex;gap:12px;padding:12px;border-bottom:1px solid #f0f0f0;cursor:pointer;">
            <img src="${imgSrc}" onerror="this.src='../../assets/img/placeholder-default.jpg'" alt="${escapeHtml(p.nama)}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;flex-shrink:0;" />
            <div class="place-item-body" style="flex:1;">
              <div class="name" style="font-weight:600;font-size:14px;color:#1e293b;">${escapeHtml(p.nama)}</div>
              <div class="desc" style="font-size:12px;color:#64748b;margin-top:2px;">${escapeHtml(p.deskripsi)}</div>
              <div class="addr" style="font-size:11px;color:#94a3b8;margin-top:4px;">📍 ${escapeHtml(p.alamat)} ${distText}</div>
            </div>
          </div>`;
      })
      .join('');

    listEl.querySelectorAll('.place-item').forEach((el) => {
      el.addEventListener('click', () => {
        const id = parseInt(el.dataset.id, 10);
        const place = allPlaces.find((p) => p.id === id);
        if (place) {
            map.flyTo([place.lat, place.lng], 18);
            if (markerById[place.id]) markerById[place.id].openPopup();
            if (window.innerWidth < 992) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
      });
    });
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  // ---------- Detail Modal ----------
  function openDetail(p) {
    if (map) map.closePopup();
    
    const imgEl = document.getElementById('detailImg');
    if (imgEl) {
      if (p.gambar) {
        imgEl.src = p.gambar.startsWith('http') ? p.gambar : `../../${p.gambar}`;
      } else {
        imgEl.src = `../../assets/img/placeholder-${p.kategori || 'default'}.jpg`;
      }
    }

    const distRow = document.getElementById('detailJarakRow');
    if (p._dist !== undefined && distRow) {
      document.getElementById('detailJarak').innerHTML = `${formatDistance(p._dist)} <br><span style="font-size:11.5px; color:var(--ink-soft); font-weight:500; display:inline-block; margin-top:3px;"><i class="fa-solid fa-car-side"></i> <i class="fa-solid fa-motorcycle"></i> Estimasi perjalanan: ${formatTravelTime(p._dist)}</span>`;
      distRow.style.display = 'flex';
    } else if (distRow) {
      distRow.style.display = 'none';
    }

    document.getElementById('detailKategori').textContent = (CFG.kategoriLabel && CFG.kategoriLabel[p.kategori]) || p.kategori;
    document.getElementById('detailNama').textContent = p.nama;
    document.getElementById('detailDeskripsi').textContent = p.deskripsi || '';
    document.getElementById('detailAlamat').textContent = p.alamat || '-';
    document.getElementById('detailKontak').textContent = p.kontak || '-';
    document.getElementById('detailJamBuka').textContent = p.jam_buka || '-';

    const btnWa = document.getElementById('btnWhatsapp');
    if (btnWa) {
      const waNumber = (p.kontak || '').replace(/[^0-9]/g, '');
      if (waNumber && waNumber.length > 5) {
        btnWa.style.display = 'inline-flex';
        btnWa.onclick = () => window.open(`https://wa.me/62${waNumber.replace(/^0/, '')}`, '_blank');
      } else {
        btnWa.style.display = 'none';
      }
    }

    const btnRute = document.getElementById('btnRute');
    if (btnRute) {
      btnRute.onclick = () => {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}`, '_blank');
      };
    }

    const overlay = document.getElementById('detailOverlay');
    if (overlay) overlay.classList.add('open');
    map.flyTo([p.lat, p.lng], 18);
  }

  // ---------- Event Listeners ----------
  const detailClose = document.getElementById('detailClose');
  if (detailClose) {
    detailClose.addEventListener('click', () => {
      document.getElementById('detailOverlay').classList.remove('open');
    });
  }

  const filterBar = document.getElementById('filterBar');
  if (filterBar) {
    filterBar.addEventListener('click', (e) => {
      const chip = e.target.closest('.filter-chip');
      if (!chip) return;
      document.querySelectorAll('.filter-chip').forEach((c) => c.classList.remove('active'));
      chip.classList.add('active');
      activeKategori = chip.dataset.kategori;
      renderAll();
    });
  }

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value;
      renderAll();
    });
  }

  const sortSelect = document.getElementById('sortSelect');
  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      sortMode = e.target.value;
      renderAll();
    });
  }

  const btnSedangBuka = document.getElementById('btnSedangBuka');
  if (btnSedangBuka) {
    btnSedangBuka.addEventListener('click', () => {
      onlyOpenNow = !onlyOpenNow;
      if (onlyOpenNow) {
        btnSedangBuka.classList.add('active');
        btnSedangBuka.classList.replace('btn-outline', 'btn-primary');
      } else {
        btnSedangBuka.classList.remove('active');
        btnSedangBuka.classList.replace('btn-primary', 'btn-outline');
      }
      renderAll();

      // Jika filter "Sedang Buka" aktif, arahkan peta ke lokasi yang sedang buka
      if (onlyOpenNow) {
        const openPlaces = getFiltered();
        if (openPlaces.length === 1) {
          // Hanya 1 tempat: langsung flyTo ke sana
          map.flyTo([openPlaces[0].lat, openPlaces[0].lng], 18);
          if (markerById[openPlaces[0].id]) {
            setTimeout(() => markerById[openPlaces[0].id].openPopup(), 800);
          }
        } else if (openPlaces.length > 1) {
          // Beberapa tempat: zoom out agar semua terlihat
          const bounds = openPlaces.map(p => [p.lat, p.lng]);
          map.flyToBounds(bounds, { padding: [40, 40], maxZoom: 17 });
        }
      }
    });
  }

  const btnLokasiSaya = document.getElementById('btnLokasiSaya');
  if (btnLokasiSaya) {
    btnLokasiSaya.addEventListener('click', () => {
      if (!navigator.geolocation) {
        alert('Geolocation tidak didukung browser ini.');
        return;
      }
      btnLokasiSaya.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mencari...';
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          userLatLng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
          btnLokasiSaya.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Lokasi Saya';
          btnLokasiSaya.classList.replace('btn-outline', 'btn-primary');
          
          if (userMarker) map.removeLayer(userMarker);
          userMarker = L.marker([userLatLng.lat, userLatLng.lng], {
            icon: L.divIcon({
              className: 'custom-user-marker',
              html: `<div style="width:16px;height:16px;background:#3b82f6;border:3px solid #fff;border-radius:50%;box-shadow:0 0 10px rgba(0,0,0,0.4);"></div>`,
              iconSize: [22, 22],
              iconAnchor: [11, 11]
            })
          }).addTo(map).bindPopup('Lokasi Anda Saat Ini').openPopup();

          map.flyTo([userLatLng.lat, userLatLng.lng], 17);
          renderAll();
        },
        (err) => {
          console.error(err);
          alert('Gagal mendapatkan lokasi Anda.');
          btnLokasiSaya.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Lokasi Saya';
        }
      );
    });
  }

  // ---------- Kick off ----------
  initMap();
  fetchPlaces();
})();