/**
 * Benzerlik Hesaplama Modülü
 * TF-IDF ve Cosine Similarity kullanarak metin benzerliği hesaplar
 * CPU dostu, tamamen local çalışır
 */

import natural from 'natural';

const TfIdf = natural.TfIdf;
const tokenizer = new natural.WordTokenizer();

/**
 * İki metin arasındaki benzerliği hesapla (0-1 arası)
 * @param {string} text1 - İlk metin
 * @param {string} text2 - İkinci metin
 * @returns {number} Benzerlik skoru (0-1)
 */
export function calculateSimilarity(text1, text2) {
  if (!text1 || !text2) return 0;

  try {
    // TF-IDF ile vektörize et
    const tfidf = new TfIdf();
    tfidf.addDocument(text1);
    tfidf.addDocument(text2);

    // İki dokümanın vektörlerini al
    const vector1 = getDocumentVector(tfidf, 0);
    const vector2 = getDocumentVector(tfidf, 1);

    // Cosine similarity hesapla
    return cosineSimilarity(vector1, vector2);
  } catch (error) {
    console.error('Benzerlik hesaplama hatası:', error.message);
    return 0;
  }
}

/**
 * Bir metni birden fazla metin ile karşılaştır, ortalama benzerlik hesapla
 * @param {string} targetText - Hedef metin
 * @param {Array<string>} referenceTexts - Referans metinler
 * @returns {number} Ortalama benzerlik (0-1)
 */
export function calculateAverageSimilarity(targetText, referenceTexts) {
  if (!targetText || !referenceTexts || referenceTexts.length === 0) {
    return 0;
  }

  const similarities = referenceTexts.map(refText => 
    calculateSimilarity(targetText, refText)
  );

  const sum = similarities.reduce((acc, val) => acc + val, 0);
  return sum / similarities.length;
}

/**
 * En benzer metni bul
 * @param {string} targetText - Hedef metin
 * @param {Object} referenceTexts - Referans metinler (key: dosya adı, value: metin)
 * @returns {Object} En benzer dosya ve skor
 */
export function findMostSimilar(targetText, referenceTexts) {
  if (!targetText || !referenceTexts || Object.keys(referenceTexts).length === 0) {
    return { fileName: null, similarity: 0 };
  }

  let maxSimilarity = 0;
  let mostSimilarFile = null;

  Object.entries(referenceTexts).forEach(([fileName, refText]) => {
    const similarity = calculateSimilarity(targetText, refText);
    if (similarity > maxSimilarity) {
      maxSimilarity = similarity;
      mostSimilarFile = fileName;
    }
  });

  return {
    fileName: mostSimilarFile,
    similarity: maxSimilarity
  };
}

/**
 * TF-IDF dokümanından vektör oluştur
 * @param {TfIdf} tfidf - TF-IDF nesnesi
 * @param {number} docIndex - Doküman indeksi
 * @returns {Map} Terim-frekans map'i
 */
function getDocumentVector(tfidf, docIndex) {
  const vector = new Map();
  
  tfidf.listTerms(docIndex).forEach(item => {
    vector.set(item.term, item.tfidf);
  });

  return vector;
}

/**
 * İki vektör arasında cosine similarity hesapla
 * @param {Map} vector1 - İlk vektör
 * @param {Map} vector2 - İkinci vektör
 * @returns {number} Cosine similarity (0-1)
 */
function cosineSimilarity(vector1, vector2) {
  // Tüm terimleri topla
  const allTerms = new Set([...vector1.keys(), ...vector2.keys()]);

  let dotProduct = 0;
  let magnitude1 = 0;
  let magnitude2 = 0;

  allTerms.forEach(term => {
    const val1 = vector1.get(term) || 0;
    const val2 = vector2.get(term) || 0;

    dotProduct += val1 * val2;
    magnitude1 += val1 * val1;
    magnitude2 += val2 * val2;
  });

  magnitude1 = Math.sqrt(magnitude1);
  magnitude2 = Math.sqrt(magnitude2);

  if (magnitude1 === 0 || magnitude2 === 0) {
    return 0;
  }

  return dotProduct / (magnitude1 * magnitude2);
}

/**
 * Jaccard benzerliği hesapla (alternatif metod)
 * @param {string} text1 - İlk metin
 * @param {string} text2 - İkinci metin
 * @returns {number} Jaccard benzerliği (0-1)
 */
export function jaccardSimilarity(text1, text2) {
  if (!text1 || !text2) return 0;

  const tokens1 = new Set(tokenizer.tokenize(text1.toLowerCase()));
  const tokens2 = new Set(tokenizer.tokenize(text2.toLowerCase()));

  // Kesişim
  const intersection = new Set([...tokens1].filter(x => tokens2.has(x)));
  
  // Birleşim
  const union = new Set([...tokens1, ...tokens2]);

  if (union.size === 0) return 0;

  return intersection.size / union.size;
}

/**
 * Levenshtein mesafesini hesapla (karakter düzeyinde benzerlik)
 * @param {string} str1 - İlk string
 * @param {string} str2 - İkinci string
 * @returns {number} Levenshtein mesafesi
 */
export function levenshteinDistance(str1, str2) {
  const matrix = [];

  for (let i = 0; i <= str2.length; i++) {
    matrix[i] = [i];
  }

  for (let j = 0; j <= str1.length; j++) {
    matrix[0][j] = j;
  }

  for (let i = 1; i <= str2.length; i++) {
    for (let j = 1; j <= str1.length; j++) {
      if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
        matrix[i][j] = matrix[i - 1][j - 1];
      } else {
        matrix[i][j] = Math.min(
          matrix[i - 1][j - 1] + 1, // substitution
          matrix[i][j - 1] + 1,     // insertion
          matrix[i - 1][j] + 1      // deletion
        );
      }
    }
  }

  return matrix[str2.length][str1.length];
}

/**
 * Levenshtein mesafesini benzerlik skoruna çevir (0-1)
 * @param {string} str1 - İlk string
 * @param {string} str2 - İkinci string
 * @returns {number} Benzerlik skoru (0-1)
 */
export function levenshteinSimilarity(str1, str2) {
  const distance = levenshteinDistance(str1, str2);
  const maxLength = Math.max(str1.length, str2.length);
  
  if (maxLength === 0) return 1;
  
  return 1 - (distance / maxLength);
}

/**
 * Hibrit benzerlik hesapla (TF-IDF + Jaccard)
 * @param {string} text1 - İlk metin
 * @param {string} text2 - İkinci metin
 * @param {number} tfidfWeight - TF-IDF ağırlığı (0-1)
 * @returns {number} Hibrit benzerlik skoru (0-1)
 */
export function hybridSimilarity(text1, text2, tfidfWeight = 0.7) {
  const tfidfSim = calculateSimilarity(text1, text2);
  const jaccardSim = jaccardSimilarity(text1, text2);
  
  return (tfidfSim * tfidfWeight) + (jaccardSim * (1 - tfidfWeight));
}

/**
 * Benzerlik skorunu yüzdeye çevir
 * @param {number} score - Benzerlik skoru (0-1)
 * @returns {number} Yüzde değeri
 */
export function toPercentage(score) {
  return Math.round(score * 100);
}

/**
 * Risk seviyesini belirle
 * @param {number} similarity - Benzerlik skoru (0-1)
 * @returns {string} Risk seviyesi
 */
export function getRiskLevel(similarity) {
  if (similarity >= 0.8) return 'Düşük';
  if (similarity >= 0.6) return 'Orta';
  if (similarity >= 0.4) return 'Yüksek';
  return 'Çok Yüksek';
}

/**
 * Risk seviyesine göre emoji al
 * @param {string} riskLevel - Risk seviyesi
 * @returns {string} Emoji
 */
export function getRiskEmoji(riskLevel) {
  const emojiMap = {
    'Düşük': '✅',
    'Orta': '⚠️',
    'Yüksek': '⛔',
    'Çok Yüksek': '🚨'
  };
  return emojiMap[riskLevel] || '❓';
}

export default {
  calculateSimilarity,
  calculateAverageSimilarity,
  findMostSimilar,
  jaccardSimilarity,
  levenshteinDistance,
  levenshteinSimilarity,
  hybridSimilarity,
  toPercentage,
  getRiskLevel,
  getRiskEmoji
};

