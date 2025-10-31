/**
 * AI Analiz Motoru Servisi
 * Firma ve kategori bazlı doküman analizi yapar
 * TF-IDF ve benzerlik algoritmalarıyla risk skoru üretir
 */

import path from 'path';
import { extractText, extractKeywords, findRiskPatterns } from './utils/extractor.js';
import { 
  calculateAverageSimilarity, 
  findMostSimilar, 
  toPercentage,
  getRiskLevel,
  getRiskEmoji
} from './utils/similarity.js';
import {
  loadFirmMemory,
  loadGlobalMemory,
  loadCategoryMemory
} from './feedback.js';

/**
 * Doküman analizi yap
 * @param {Object} document - Doküman objesi
 * @param {string} storagePath - Dosya depolama yolu
 * @returns {Promise<Object>} Analiz sonucu
 */
export async function analyzeDocument(document, storagePath) {
  try {
    console.log(`🔍 Analiz başlatılıyor: ${document.file_path}`);

    // Dosya yolunu oluştur
    const filePath = path.join(storagePath, document.file_path);

    // Metin çıkar
    const extractedText = await extractText(filePath);
    
    if (!extractedText || extractedText.length < 10) {
      return {
        success: false,
        message: 'Dosyadan yeterli metin çıkarılamadı',
        document,
        extractedText: ''
      };
    }

    console.log(`📄 Metin çıkarıldı: ${extractedText.substring(0, 100)}...`);

    // Firma hafızasını yükle
    const firmMemory = loadFirmMemory(document.user_id);
    
    // Kategori bilgilerini al
    const categoryMemory = loadCategoryMemory();
    const categoryName = document.category?.name || 'default';
    const categoryInfo = categoryMemory[categoryName] || categoryMemory.default;

    // Global hafızayı yükle
    const globalMemory = loadGlobalMemory();

    // Analiz yap
    const analysis = performAnalysis(
      extractedText,
      firmMemory,
      categoryInfo,
      globalMemory,
      document
    );

    return {
      success: true,
      document,
      extractedText,
      analysis
    };

  } catch (error) {
    console.error(`❌ Analiz hatası [${document.id}]:`, error.message);
    return {
      success: false,
      message: error.message,
      document,
      extractedText: ''
    };
  }
}

/**
 * Ana analiz fonksiyonu
 * @param {string} text - Çıkarılan metin
 * @param {Object} firmMemory - Firma hafızası
 * @param {Object} categoryInfo - Kategori bilgileri
 * @param {Object} globalMemory - Global hafıza
 * @param {Object} document - Doküman objesi
 * @returns {Object} Analiz sonucu
 */
function performAnalysis(text, firmMemory, categoryInfo, globalMemory, document) {
  const result = {
    similarity_score: 0,
    risk_level: 'Bilinmiyor',
    risk_emoji: '❓',
    comments: [],
    recommendations: [],
    keyword_analysis: {},
    risk_patterns_found: []
  };

  // 1. Firma bazlı benzerlik analizi (yıl bazlı)
  // Yıl yoksa doküman oluşturma tarihinden al
  const fileYear = document.file_year || 
                   (document.created_at ? new Date(document.created_at).getFullYear().toString() : null) ||
                   new Date().getFullYear().toString();
  const years = firmMemory.years || {};
  
  // Aynı yıl ve bir önceki yıl verilerini al
  const currentYear = years[fileYear] || { approved: {}, rejected: {} };
  const prevYear = years[(parseInt(fileYear) - 1).toString()] || { approved: {}, rejected: {} };
  
  // Tüm onaylı metinleri birleştir (öncelik: aynı yıl, sonra önceki yıl)
  const approvedTexts = [
    ...Object.values(currentYear.approved || {}),
    ...Object.values(prevYear.approved || {})
  ];
  
  const rejectedTexts = [
    ...Object.values(currentYear.rejected || {}).map(r => r.text || r),
    ...Object.values(prevYear.rejected || {}).map(r => r.text || r)
  ];

  if (approvedTexts.length > 0) {
    // Onaylı dosyalarla benzerliği hesapla
    const avgSimilarity = calculateAverageSimilarity(text, approvedTexts);
    result.similarity_score = avgSimilarity;
    result.similarity_percentage = toPercentage(avgSimilarity);

    // En benzer onaylı dosyayı bul (yıl bazlı)
    const allApprovedFiles = {
      ...currentYear.approved,
      ...prevYear.approved
    };
    
    const mostSimilar = findMostSimilar(text, allApprovedFiles);
    
    if (mostSimilar.fileName) {
      result.comments.push(
        `Bu dosya, firmanın "${mostSimilar.fileName}" dosyasına %${toPercentage(mostSimilar.similarity)} benzerlik gösteriyor.`
      );
    }
    
    // Yıl bilgisi ekle
    if (approvedTexts.length > 0) {
      const currentYearCount = Object.keys(currentYear.approved || {}).length;
      const prevYearCount = Object.keys(prevYear.approved || {}).length;
      
      if (currentYearCount > 0) {
        result.comments.push(`${fileYear} yılı için ${currentYearCount} onaylı dosya mevcut.`);
      }
      if (prevYearCount > 0) {
        result.comments.push(`${parseInt(fileYear) - 1} yılı verisi de kullanıldı.`);
      }
    }

    // Benzerlik yorumu
    if (avgSimilarity >= 0.8) {
      result.comments.push('Firma geçmişinde benzer onaylı dosyalar mevcut.');
      result.recommendations.push('Onay için yüksek güvenilirlik.');
    } else if (avgSimilarity >= 0.6) {
      result.comments.push('Firma geçmişine kısmen benzer.');
      result.recommendations.push('Manuel kontrol önerilir.');
    } else {
      result.comments.push('Firma geçmişine düşük benzerlik.');
      result.recommendations.push('Detaylı inceleme gerekebilir.');
    }
  } else {
    // Yeni firma - global ortalamayı kullan
    result.similarity_score = globalMemory.average_similarity || 0.74;
    result.similarity_percentage = toPercentage(result.similarity_score);
    result.comments.push('Bu firma için henüz onaylı dosya bulunmuyor.');
    result.recommendations.push('İlk dosya - manual onay önerilir.');
  }

  // 2. Reddedilen dosyalarla karşılaştırma
  if (rejectedTexts.length > 0) {
    const rejectedSimilarity = calculateAverageSimilarity(text, rejectedTexts);
    
    if (rejectedSimilarity > 0.7) {
      result.comments.push(
        `⚠️ Reddedilen dosyalara %${toPercentage(rejectedSimilarity)} benzerlik tespit edildi!`
      );
      result.risk_level = 'Yüksek';
      result.recommendations.push('Reddedilen dosya benzerligi - dikkatli inceleyin.');
    }
  }

  // 3. Risk pattern kontrolü
  const firmRiskPatterns = firmMemory.stats?.risk_keywords || [];
  const categoryRiskPatterns = categoryInfo.risk_patterns || [];
  const allRiskPatterns = [...new Set([...firmRiskPatterns, ...categoryRiskPatterns])];

  const foundRisks = findRiskPatterns(text, allRiskPatterns);
  
  if (foundRisks.length > 0) {
    result.risk_patterns_found = foundRisks;
    result.comments.push(`🚨 Risk ifadeleri bulundu: ${foundRisks.join(', ')}`);
    
    // Risk seviyesini artır
    if (result.similarity_score < 0.6) {
      result.risk_level = 'Çok Yüksek';
    } else if (result.risk_level !== 'Yüksek') {
      result.risk_level = 'Orta';
    }
  }

  // 4. Kategori anahtar kelime analizi
  const categoryKeywords = categoryInfo.keywords || [];
  const extractedKeywords = extractKeywords(text, 10);
  
  const matchingKeywords = extractedKeywords.filter(kw => 
    categoryKeywords.some(ck => kw.includes(ck) || ck.includes(kw))
  );

  result.keyword_analysis = {
    category_keywords: categoryKeywords,
    extracted_keywords: extractedKeywords,
    matching_keywords: matchingKeywords,
    match_rate: categoryKeywords.length > 0 
      ? (matchingKeywords.length / categoryKeywords.length) 
      : 0
  };

  if (matchingKeywords.length > 0) {
    result.comments.push(
      `✅ Kategori anahtar kelimeleri bulundu: ${matchingKeywords.slice(0, 5).join(', ')}`
    );
  } else if (categoryKeywords.length > 0) {
    result.comments.push(
      `⚠️ Beklenen kategori anahtar kelimeleri eksik.`
    );
    result.recommendations.push('Dosya formatını kontrol edin.');
  }

  // 5. Final risk seviyesi
  if (!result.risk_level || result.risk_level === 'Bilinmiyor') {
    result.risk_level = getRiskLevel(result.similarity_score);
  }
  
  result.risk_emoji = getRiskEmoji(result.risk_level);

  // 6. Kategori istatistikleri
  const categoryStats = firmMemory.categories?.[document.category?.name] || null;
  if (categoryStats) {
    result.category_stats = {
      approved: categoryStats.approved || 0,
      rejected: categoryStats.rejected || 0,
      total: (categoryStats.approved || 0) + (categoryStats.rejected || 0)
    };
  }

  return result;
}

/**
 * Birden fazla dokümanı toplu analiz et
 * @param {Array<Object>} documents - Doküman listesi
 * @param {string} storagePath - Dosya depolama yolu
 * @returns {Promise<Array<Object>>} Analiz sonuçları
 */
export async function analyzeBatch(documents, storagePath) {
  console.log(`📊 Toplu analiz başlatılıyor: ${documents.length} dosya`);
  
  const results = [];
  
  for (const doc of documents) {
    const result = await analyzeDocument(doc, storagePath);
    results.push(result);
    
    // CPU'ya mola ver
    await new Promise(resolve => setTimeout(resolve, 100));
  }

  return results;
}

/**
 * Firma özet raporu oluştur
 * @param {number} userId - Kullanıcı ID
 * @returns {Object} Firma raporu
 */
export function generateFirmReport(userId) {
  const firmMemory = loadFirmMemory(userId);
  
  // Yıl bazlı hafızadan toplam sayıları hesapla
  const years = firmMemory.years || {};
  let totalApproved = 0;
  let totalRejected = 0;
  
  Object.values(years).forEach(yearData => {
    totalApproved += Object.keys(yearData.approved || {}).length;
    totalRejected += Object.keys(yearData.rejected || {}).length;
  });
  
  const total = totalApproved + totalRejected;
  const approvalRate = total > 0 ? (totalApproved / total) * 100 : 0;

  // Yıl bazlı özet
  const yearSummary = {};
  Object.keys(years).forEach(year => {
    const yearData = years[year];
    yearSummary[year] = {
      approved: Object.keys(yearData.approved || {}).length,
      rejected: Object.keys(yearData.rejected || {}).length
    };
  });

  return {
    userId,
    stats: {
      total_documents: total,
      total_approved: totalApproved,
      total_rejected: totalRejected,
      approval_rate: approvalRate.toFixed(1),
      avg_similarity: firmMemory.stats?.avg_similarity || 0,
      risk_keywords: firmMemory.stats?.risk_keywords || []
    },
    years: yearSummary,
    categories: firmMemory.categories || {},
    last_updated: firmMemory.last_updated
  };
}

/**
 * Kategori bazlı analiz raporu
 * @param {number} userId - Kullanıcı ID
 * @returns {Object} Kategori raporu
 */
export function generateCategoryReport(userId) {
  const firmMemory = loadFirmMemory(userId);
  const categories = firmMemory.categories || {};

  const report = {};

  Object.keys(categories).forEach(categoryName => {
    const stats = categories[categoryName];
    const total = stats.approved + stats.rejected + (stats.pending || 0);
    
    report[categoryName] = {
      approved: stats.approved,
      rejected: stats.rejected,
      pending: stats.pending || 0,
      total,
      approval_rate: total > 0 ? ((stats.approved / total) * 100).toFixed(1) : 0
    };
  });

  return report;
}

/**
 * Riskli dosyaları listele
 * @param {Array<Object>} analysisResults - Analiz sonuçları
 * @returns {Array<Object>} Riskli dosyalar
 */
export function getRiskyDocuments(analysisResults) {
  return analysisResults
    .filter(result => 
      result.success && 
      (result.analysis.risk_level === 'Yüksek' || result.analysis.risk_level === 'Çok Yüksek')
    )
    .sort((a, b) => a.analysis.similarity_score - b.analysis.similarity_score);
}

/**
 * Global sistem istatistikleri
 * @returns {Object} Sistem istatistikleri
 */
export function getSystemStats() {
  const global = loadGlobalMemory();
  
  return {
    total_analyzed: global.total_documents_analyzed || 0,
    total_approved: global.total_approved || 0,
    total_rejected: global.total_rejected || 0,
    total_pending: global.total_pending || 0,
    average_similarity: global.average_similarity || 0,
    common_risks: global.most_common_risks || [],
    last_updated: global.last_updated
  };
}

export default {
  analyzeDocument,
  analyzeBatch,
  generateFirmReport,
  generateCategoryReport,
  getRiskyDocuments,
  getSystemStats
};

