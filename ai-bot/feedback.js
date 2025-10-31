/**
 * Feedback ve Hafıza Güncelleme Modülü
 * Onay/red kararlarını kaydeder ve firma hafızasını günceller
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const MEMORY_PATH = path.join(__dirname, 'utils', 'memory');
const FIRMS_PATH = path.join(MEMORY_PATH, 'firms');
const FEEDBACKS_FILE = path.join(MEMORY_PATH, 'feedbacks.json');
const GLOBAL_FILE = path.join(MEMORY_PATH, 'global.json');
const CATEGORIES_FILE = path.join(MEMORY_PATH, 'categories.json');

/**
 * Firma hafızası yükle veya oluştur
 * @param {number} userId - Kullanıcı ID
 * @returns {Object} Firma hafızası
 */
export function loadFirmMemory(userId) {
  const firmFile = path.join(FIRMS_PATH, `${userId}.json`);
  
  if (fs.existsSync(firmFile)) {
    try {
      const data = fs.readFileSync(firmFile, 'utf-8');
      return JSON.parse(data);
    } catch (error) {
      console.error(`Firma hafızası okuma hatası [${userId}]:`, error.message);
    }
  }

  // Yeni firma hafızası oluştur (yıl bazlı)
  return {
    userId,
    years: {}, // Yıl bazlı hafıza: { "2024": { approved: {}, rejected: {} } }
    stats: {
      total_documents: 0,
      total_approved: 0,
      total_rejected: 0,
      avg_similarity: 0,
      risk_keywords: []
    },
    categories: {},
    last_updated: new Date().toISOString()
  };
}

/**
 * Firma hafızasını kaydet
 * @param {number} userId - Kullanıcı ID
 * @param {Object} memory - Hafıza objesi
 */
export function saveFirmMemory(userId, memory) {
  try {
    const firmFile = path.join(FIRMS_PATH, `${userId}.json`);
    memory.last_updated = new Date().toISOString();
    
    fs.writeFileSync(firmFile, JSON.stringify(memory, null, 2), 'utf-8');
    console.log(`✅ Firma hafızası kaydedildi: User ${userId}`);
  } catch (error) {
    console.error(`Firma hafızası kaydetme hatası [${userId}]:`, error.message);
  }
}

/**
 * Global hafızayı yükle
 * @returns {Object} Global hafıza
 */
export function loadGlobalMemory() {
  try {
    if (fs.existsSync(GLOBAL_FILE)) {
      const data = fs.readFileSync(GLOBAL_FILE, 'utf-8');
      return JSON.parse(data);
    }
  } catch (error) {
    console.error('Global hafıza okuma hatası:', error.message);
  }

  return {
    average_similarity: 0.74,
    total_documents_analyzed: 0,
    total_approved: 0,
    total_rejected: 0,
    total_pending: 0,
    most_common_risks: [],
    last_updated: null
  };
}

/**
 * Global hafızayı kaydet
 * @param {Object} memory - Global hafıza objesi
 */
export function saveGlobalMemory(memory) {
  try {
    memory.last_updated = new Date().toISOString();
    fs.writeFileSync(GLOBAL_FILE, JSON.stringify(memory, null, 2), 'utf-8');
    console.log('✅ Global hafıza güncellendi');
  } catch (error) {
    console.error('Global hafıza kaydetme hatası:', error.message);
  }
}

/**
 * Kategori hafızasını yükle
 * @returns {Object} Kategori hafızası
 */
export function loadCategoryMemory() {
  try {
    if (fs.existsSync(CATEGORIES_FILE)) {
      const data = fs.readFileSync(CATEGORIES_FILE, 'utf-8');
      return JSON.parse(data);
    }
  } catch (error) {
    console.error('Kategori hafızası okuma hatası:', error.message);
  }

  return {
    default: {
      keywords: ['belge', 'evrak', 'dosya'],
      risk_patterns: ['eksik', 'hatalı', 'yanlış'],
      weight: 1.0
    }
  };
}

/**
 * Kategori hafızasını kaydet
 * @param {Object} memory - Kategori hafızası
 */
export function saveCategoryMemory(memory) {
  try {
    fs.writeFileSync(CATEGORIES_FILE, JSON.stringify(memory, null, 2), 'utf-8');
    console.log('✅ Kategori hafızası güncellendi');
  } catch (error) {
    console.error('Kategori hafızası kaydetme hatası:', error.message);
  }
}

/**
 * Feedback kaydet
 * @param {Object} feedback - Feedback objesi
 */
export function saveFeedback(feedback) {
  try {
    let feedbacks = { feedbacks: [], last_updated: null };
    
    if (fs.existsSync(FEEDBACKS_FILE)) {
      const data = fs.readFileSync(FEEDBACKS_FILE, 'utf-8');
      feedbacks = JSON.parse(data);
    }

    feedbacks.feedbacks.push({
      ...feedback,
      timestamp: new Date().toISOString()
    });

    feedbacks.last_updated = new Date().toISOString();

    fs.writeFileSync(FEEDBACKS_FILE, JSON.stringify(feedbacks, null, 2), 'utf-8');
    console.log('✅ Feedback kaydedildi');
  } catch (error) {
    console.error('Feedback kaydetme hatası:', error.message);
  }
}

/**
 * Onay feedback'i işle ve hafızayı güncelle (yıl bazlı)
 * @param {Object} document - Doküman objesi
 * @param {string} extractedText - Çıkarılan metin
 */
export function processApprovalFeedback(document, extractedText) {
  try {
    const userId = document.user_id;
    const fileName = path.basename(document.file_path);
    const categoryName = document.category?.name || 'Diğer';
    // Yıl yoksa doküman oluşturma tarihinden al, o da yoksa şu anki yıl
    const fileYear = document.file_year || 
                     (document.created_at ? new Date(document.created_at).getFullYear().toString() : null) ||
                     new Date().getFullYear().toString();

    // Firma hafızasını yükle
    const firmMemory = loadFirmMemory(userId);

    // Yıl yapısını oluştur (yoksa)
    if (!firmMemory.years) {
      firmMemory.years = {};
    }
    if (!firmMemory.years[fileYear]) {
      firmMemory.years[fileYear] = {
        approved: {},
        rejected: {}
      };
    }

    // Onaylı dosyalara ekle (yıl bazlı)
    firmMemory.years[fileYear].approved[fileName] = extractedText;

    // İstatistikleri güncelle
    firmMemory.stats.total_documents++;
    firmMemory.stats.total_approved++;

    // Kategori istatistikleri
    if (!firmMemory.categories[categoryName]) {
      firmMemory.categories[categoryName] = {
        approved: 0,
        rejected: 0,
        pending: 0
      };
    }
    firmMemory.categories[categoryName].approved++;

    // Hafızayı kaydet
    saveFirmMemory(userId, firmMemory);

    // Global hafızayı güncelle
    updateGlobalMemory('approved');

    // Feedback kaydet
    saveFeedback({
      userId,
      fileName,
      category: categoryName,
      action: 'approved',
      file_path: document.file_path
    });

    console.log(`✅ Onay feedback'i işlendi: ${fileName} (User ${userId})`);
  } catch (error) {
    console.error('Onay feedback işleme hatası:', error.message);
  }
}

/**
 * Red feedback'i işle ve hafızayı güncelle (yıl bazlı)
 * @param {Object} document - Doküman objesi
 * @param {string} extractedText - Çıkarılan metin
 * @param {string} rejectionNote - Red notu
 */
export function processRejectionFeedback(document, extractedText, rejectionNote) {
  try {
    const userId = document.user_id;
    const fileName = path.basename(document.file_path);
    const categoryName = document.category?.name || 'Diğer';
    // Yıl yoksa doküman oluşturma tarihinden al, o da yoksa şu anki yıl
    const fileYear = document.file_year || 
                     (document.created_at ? new Date(document.created_at).getFullYear().toString() : null) ||
                     new Date().getFullYear().toString();

    // Firma hafızasını yükle
    const firmMemory = loadFirmMemory(userId);

    // Yıl yapısını oluştur (yoksa)
    if (!firmMemory.years) {
      firmMemory.years = {};
    }
    if (!firmMemory.years[fileYear]) {
      firmMemory.years[fileYear] = {
        approved: {},
        rejected: {}
      };
    }

    // Reddedilen dosyalara ekle (yıl bazlı)
    firmMemory.years[fileYear].rejected[fileName] = {
      text: extractedText,
      reason: rejectionNote || 'Belirtilmemiş'
    };

    // İstatistikleri güncelle
    firmMemory.stats.total_documents++;
    firmMemory.stats.total_rejected++;

    // Risk anahtar kelimelerini güncelle
    if (rejectionNote) {
      const keywords = extractRiskKeywords(rejectionNote);
      keywords.forEach(keyword => {
        if (!firmMemory.stats.risk_keywords.includes(keyword)) {
          firmMemory.stats.risk_keywords.push(keyword);
        }
      });
    }

    // Kategori istatistikleri
    if (!firmMemory.categories[categoryName]) {
      firmMemory.categories[categoryName] = {
        approved: 0,
        rejected: 0,
        pending: 0
      };
    }
    firmMemory.categories[categoryName].rejected++;

    // Hafızayı kaydet
    saveFirmMemory(userId, firmMemory);

    // Global hafızayı güncelle
    updateGlobalMemory('rejected', rejectionNote);

    // Kategori risk pattern'ini güncelle
    updateCategoryRiskPatterns(categoryName, rejectionNote);

    // Feedback kaydet
    saveFeedback({
      userId,
      fileName,
      category: categoryName,
      action: 'rejected',
      reason: rejectionNote,
      file_path: document.file_path
    });

    console.log(`❌ Red feedback'i işlendi: ${fileName} (User ${userId})`);
  } catch (error) {
    console.error('Red feedback işleme hatası:', error.message);
  }
}

/**
 * Global hafızayı güncelle
 * @param {string} action - İşlem tipi (approved/rejected)
 * @param {string} rejectionNote - Red notu (opsiyonel)
 */
function updateGlobalMemory(action, rejectionNote = null) {
  const global = loadGlobalMemory();

  global.total_documents_analyzed++;
  
  if (action === 'approved') {
    global.total_approved++;
  } else if (action === 'rejected') {
    global.total_rejected++;
    
    if (rejectionNote) {
      const risks = extractRiskKeywords(rejectionNote);
      risks.forEach(risk => {
        if (!global.most_common_risks.includes(risk)) {
          global.most_common_risks.push(risk);
        }
      });
    }
  }

  saveGlobalMemory(global);
}

/**
 * Kategori risk pattern'lerini güncelle
 * @param {string} categoryName - Kategori adı
 * @param {string} rejectionNote - Red notu
 */
function updateCategoryRiskPatterns(categoryName, rejectionNote) {
  if (!rejectionNote) return;

  const categories = loadCategoryMemory();

  if (!categories[categoryName]) {
    categories[categoryName] = {
      keywords: [],
      risk_patterns: [],
      weight: 1.0
    };
  }

  const risks = extractRiskKeywords(rejectionNote);
  risks.forEach(risk => {
    if (!categories[categoryName].risk_patterns.includes(risk)) {
      categories[categoryName].risk_patterns.push(risk);
    }
  });

  saveCategoryMemory(categories);
}

/**
 * Metinden risk anahtar kelimelerini çıkar
 * @param {string} text - Metin
 * @returns {Array<string>} Risk kelimeleri
 */
function extractRiskKeywords(text) {
  if (!text) return [];

  const lowerText = text.toLowerCase();
  const riskKeywords = [];

  const riskWords = [
    'eksik', 'hatalı', 'yanlış', 'tarih', 'format', 'satır',
    'tutarsız', 'uyumsuz', 'geçersiz', 'belirsiz', 'boş',
    'düşük', 'yetersiz', 'yanlis', 'hatali'
  ];

  riskWords.forEach(word => {
    if (lowerText.includes(word)) {
      riskKeywords.push(word);
    }
  });

  return riskKeywords;
}

/**
 * Tüm firma hafızalarını listele
 * @returns {Array<Object>} Firma bilgileri
 */
export function listAllFirms() {
  try {
    const files = fs.readdirSync(FIRMS_PATH);
    const firms = [];

    files.forEach(file => {
      if (file.endsWith('.json')) {
        const userId = parseInt(file.replace('.json', ''));
        const memory = loadFirmMemory(userId);
        
        firms.push({
          userId,
          stats: memory.stats,
          lastUpdated: memory.last_updated
        });
      }
    });

    return firms;
  } catch (error) {
    console.error('Firma listesi okuma hatası:', error.message);
    return [];
  }
}

export default {
  loadFirmMemory,
  saveFirmMemory,
  loadGlobalMemory,
  saveGlobalMemory,
  loadCategoryMemory,
  saveCategoryMemory,
  saveFeedback,
  processApprovalFeedback,
  processRejectionFeedback,
  listAllFirms
};

