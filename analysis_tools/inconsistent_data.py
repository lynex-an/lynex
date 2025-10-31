import sys
import pandas as pd
import shutil 
import re 
from dateutil import parser
from datetime import datetime

# 🛑 1. الحل النهائي لمشكلة الترميز (UnicodeEncodeError) 🛑
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

# دالة مساعدة لمحاولة تحويل سلسلة نصية إلى تاريخ
def parse_date_safely(date_str):
    if pd.isna(date_str) or not isinstance(date_str, str):
        return date_str
    try:
        # محاولة تحليل التاريخ بمرونة
        date_obj = parser.parse(date_str)
        # توحيد التنسيق إلى (YYYY-MM-DD)
        return date_obj.strftime('%Y-%m-%d')
    except Exception:
        # إذا فشل التحويل، نرجع القيمة الأصلية
        return date_str


def main():
    # 🛑 2. التحقق من استقبال 6 مُعاملات 🛑
    # 1: input_path, 2: columns_param, 3: threshold_param, 4: output_path, 5: mode_param
    if len(sys.argv) < 6: 
        print("Error: Missing parameters. Expected 6 arguments.")
        sys.exit(1)

    input_path = sys.argv[1]
    # 🎯 استلام المتغيرات الجديدة من PHP
    columns_param = sys.argv[2] 
    output_path = sys.argv[4]
    # mode_param = sys.argv[5] 

    # 3. تحميل البيانات
    try:
        if input_path.lower().endswith(('.xlsx', '.xls')):
            df = pd.read_excel(input_path)
        else:
            df = pd.read_csv(input_path, encoding='utf-8') 
            
    except Exception as e:
        print(f"File Load Error: {e}")
        sys.exit(1)

    # 4. منطق التحليل والمعالجة (البيانات غير المتناسقة)
    df_cleaned = df.copy()
    issues_found = False
    
    # 4.1 معالجة مُعامل الأعمدة (Columns Parameter)
    if columns_param.lower() == 'all':
        # إذا كانت القيمة 'all'، نطبق المنطق على جميع الأعمدة
        subset_cols = df_cleaned.columns.tolist()
    else:
        # إذا تم تحديد أعمدة، نستخدمها
        subset_cols = [col.strip() for col in columns_param.split(',')]
        
        # تحقق: التأكد من أن الأعمدة موجودة
        missing_cols_in_data = [col for col in subset_cols if col not in df_cleaned.columns]
        if missing_cols_in_data:
            print(f"Error: Columns not found in file: {', '.join(missing_cols_in_data)}")
            sys.exit(1)

    # 4.2 تطبيق معالجة التناقضات
    for col in subset_cols:
        
        # نحفظ السلسلة الأصلية للمقارنة
        initial_series = df_cleaned[col].copy() 

        # نطبق المنطق فقط على الأعمدة النصية (object/string)
        if df_cleaned[col].dtype == 'object':
            
            # 1. تنظيف المسافات البيضاء (Whitespace)
            try:
                # إزالة المسافات الزائدة في البداية والنهاية
                df_cleaned[col] = df_cleaned[col].str.strip()
                # استبدال المسافات المتعددة بمسافة واحدة
                df_cleaned[col] = df_cleaned[col].str.replace(r'\s+', ' ', regex=True)
            except Exception:
                 pass
                 
            # 2. توحيد حالة الأحرف (Lowercasing) للغة الإنجليزية/اللاتينية فقط
            try:
                # نتحقق مما إذا كانت جميع القيم لا تحتوي على أحرف عربية (Unicode range \u0600-\u06FF)
                is_mostly_non_arabic = df_cleaned[col].dropna().apply(
                    lambda x: isinstance(x, str) and not bool(re.search(r'[\u0600-\u06FF]', x))
                ).all()

                if is_mostly_non_arabic:
                    # تطبيق التحويل إلى حروف صغيرة لتوحيد الكتابة (مثلاً: 'USA' و 'usa')
                    df_cleaned[col] = df_cleaned[col].str.lower()
                    
            except Exception:
                pass
                
            # 3. توحيد تنسيق التاريخ (Date Format Standardization)
            # هذه الخطوة مهمة جداً لمعالجة التناقضات
            # نتحقق من وجود ما يشبه تنسيق التاريخ (على الأقل قيمتين نصيتين في العمود)
            non_na_values = df_cleaned[col].dropna()
            if len(non_na_values) > 1 and non_na_values.iloc[:2].apply(lambda x: isinstance(x, str)).all():
                # نطبق دالة تحليل التاريخ الآمنة
                df_cleaned[col] = df_cleaned[col].apply(parse_date_safely)
                
            # 4. التحقق مما إذا كان هناك تغيير قد حدث
            if not initial_series.equals(df_cleaned[col]):
                issues_found = True

    # 5. حفظ الملف والإخراج المبسط لـ PHP 
    if not issues_found:
        shutil.copy(input_path, output_path)
        print("No issues found")
    else:
        # إذا تم العثور على مشاكل وتم تصحيحها
        if output_path.lower().endswith(('.xlsx', '.xls')):
            df_cleaned.to_excel(output_path, index=False)
        else:
            df_cleaned.to_csv(output_path, index=False, encoding='utf-8')
            
        print("Cleaned file saved") 

if __name__ == "__main__":
    main()
