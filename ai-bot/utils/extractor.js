/**
 * Dosya Metin Çıkarıcı Modülü
 * PDF, XLSX, DOCX dosyalarından metin içeriği çıkarır
 * pdf2json kullanılıyor (pdf-parse yerine - daha stabil)
 */

import fs from 'fs';
import path from 'path';
import PDFParser from 'pdf2json';
import xlsx from 'xlsx';
import mammoth from 'mammoth';

export async function extractText(filePath) {
  try {
    if (!fs.existsSync(filePath)) {
      throw new Error(`Dosya bulunamadı: ${filePath}`);
    }

    const ext = path.extname(filePath).toLowerCase();
    
    switch (ext) {
      case '.pdf':
        return await extractFromPDF(filePath);
      case '.xlsx':
      case '.xls':
        return await extractFromExcel(filePath);
      case '.docx':
        return await extractFromDocx(filePath);
      case '.txt':
        return await extractFromText(filePath);
      default:
        console.warn(`Desteklenmeyen dosya türü: ${ext}`);
        return '';
    }
  } catch (error) {
    console.error(`Metin çıkarma hatası [${filePath}]:`, error.message);
    return '';
  }
}

/**
 * PDF dosyasından metin çıkar (pdf2json kullanarak)
 */
async function extractFromPDF(filePath) {
  return new Promise((resolve) => {
    const pdfParser = new PDFParser(null, 1);
    
    pdfParser.on('pdfParser_dataError', errData => {
      console.error('PDF okuma hatası:', errData.parserError);
      resolve(''); // Hata olursa boş döndür, crash etme
    });
    
    pdfParser.on('pdfParser_dataReady', () => {
      try {
        // Ham metni al
        const rawText = pdfParser.getRawTextContent();
        resolve(cleanText(rawText));
      } catch (error) {
        console.error('PDF metin çıkarma hatası:', error.message);
        resolve('');
      }
    });
    
    // PDF'i yükle
    pdfParser.loadPDF(filePath);
  });
}

async function extractFromExcel(filePath) {
  try {
    const workbook = xlsx.readFile(filePath);
    let allText = '';
    workbook.SheetNames.forEach(sheetName => {
      const sheet = workbook.Sheets[sheetName];
      const sheetData = xlsx.utils.sheet_to_json(sheet, { header: 1, defval: '' });
      sheetData.forEach(row => {
        const rowText = row.join(' ');
        allText += rowText + '\n';
      });
    });
    return cleanText(allText);
  } catch (error) {
    console.error('Excel okuma hatası:', error.message);
    return '';
  }
}

async function extractFromDocx(filePath) {
  try {
    const buffer = fs.readFileSync(filePath);
    const result = await mammoth.extractRawText({ buffer });
    return cleanText(result.value);
  } catch (error) {
    console.error('DOCX okuma hatası:', error.message);
    return '';
  }
}

async function extractFromText(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf-8');
    return cleanText(content);
  } catch (error) {
    console.error('TXT okuma hatası:', error.message);
    return '';
  }
}

function cleanText(text) {
  if (!text) return '';
  return text
    .replace(/\s+/g, ' ')
    .replace(/[\r\n]+/g, '\n')
    .trim()
    .toLowerCase();
}

export function extractKeywords(text, topN = 20) {
  if (!text) return [];
  const stopWords = new Set(['ve', 'veya', 'ile', 'de', 'da', 'ama', 'fakat', 'için', 'bir', 'bu', 'şu', 'ki', 'mi', 'mı', 'mu', 'mü', 'ne', 'nasıl', 'gibi', 'kadar', 'daha', 'en', 'çok', 'az', 'var', 'yok', 'the', 'a', 'an', 'and', 'or', 'but']);
  const words = text.toLowerCase().replace(/[^\wğüşöçıİĞÜŞÖÇ\s]/g, '').split(/\s+/).filter(word => word.length > 2 && !stopWords.has(word));
  const frequency = {};
  words.forEach(word => { frequency[word] = (frequency[word] || 0) + 1; });
  return Object.entries(frequency).sort((a, b) => b[1] - a[1]).slice(0, topN).map(([word]) => word);
}

export function findRiskPatterns(text, riskPatterns = []) {
  if (!text || !riskPatterns.length) return [];
  const foundRisks = [];
  const lowerText = text.toLowerCase();
  riskPatterns.forEach(pattern => {
    if (lowerText.includes(pattern.toLowerCase())) foundRisks.push(pattern);
  });
  return foundRisks;
}

export function getFileMetadata(filePath) {
  try {
    if (!fs.existsSync(filePath)) return null;
    const stats = fs.statSync(filePath);
    return {
      name: path.basename(filePath),
      extension: path.extname(filePath).toLowerCase(),
      size: stats.size,
      created: stats.birthtime,
      modified: stats.mtime,
      sizeKB: (stats.size / 1024).toFixed(2)
    };
  } catch (error) {
    console.error('Meta bilgi okuma hatası:', error.message);
    return null;
  }
}

export default { extractText, extractKeywords, findRiskPatterns, getFileMetadata };