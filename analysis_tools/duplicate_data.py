import sys
import pandas as pd
import shutil 

# 🛑 1. الحل النهائي لمشكلة الترميز (UnicodeEncodeError) 🛑
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')


def main():
    # 🛑 2. التحقق من استقبال 6 مُعاملات 🛑
    # 1: input_path, 2: columns, 3: threshold, 4: output_path, 5: mode
    if len(sys.argv) < 6: 
        print("Error: Missing parameters. Expected 6 arguments.")
        sys.exit(1)

    input_path = sys.argv[1]
    # 🎯 استلام المتغيرات الجديدة من PHP
    columns_param = sys.argv[2] 
    threshold_param = sys.argv[3] 
    output_path = sys.argv[4]
    mode_param = sys.argv[5] 

    # 3. تحميل البيانات
    try:
        # دعم قراءة ملفات Excel
        if input_path.lower().endswith(('.xlsx', '.xls')):
            df = pd.read_excel(input_path)
        else:
            # قراءة CSV بترميمز UTF-8
            df = pd.read_csv(input_path, encoding='utf-8') 
            
    except Exception as e:
        print(f"File Load Error: {e}")
        sys.exit(1)

    # 4. منطق التحليل والمعالجة (التكرارات)
    original_rows = len(df)
    
    # 4.1 معالجة مُعامل الأعمدة (Columns Parameter)
    if columns_param.lower() == 'all':
        subset_cols = None # إذا كانت القيمة 'all'، فسيتم البحث في جميع الأعمدة
    else:
        # تحويل السلسلة النصية للأعمدة (التي تكون مفصولة بفواصل) إلى قائمة
        subset_cols = [col.strip() for col in columns_param.split(',')]
        
        # 💡 تحقق: التأكد من أن الأعمدة موجودة بالفعل في البيانات
        missing_cols = [col for col in subset_cols if col not in df.columns]
        if missing_cols:
            print(f"Error: Columns not found in file: {', '.join(missing_cols)}")
            sys.exit(1)
            
    # 4.2 حذف التكرارات الدقيقة (Hard Duplicates)
    # استخدام `subset=subset_cols` يضمن حذف التكرارات بناءً على الأعمدة المطلوبة فقط.
    df_cleaned = df.drop_duplicates(subset=subset_cols, keep='first')
    
    cleaned_rows = len(df_cleaned)
    issues_found = original_rows != cleaned_rows 
    
    # 📌 تحديث مستقبلي (ميزة المطابقة التقريبية - Fuzzy Matching):
    # إذا كنت تريد تطبيق المطابقة التقريبية باستخدام threshold_param:
    # يجب تثبيت مكتبات إضافية مثل fuzzywuzzy واستخدام خوارزميات مقارنة السلاسل النصية.
    # حالياً، يتم استخدام threshold_param فقط كعنصر نائب لميزة مستقبلية.

    # 5. حفظ الملف والإخراج المبسط لـ PHP 
    if not issues_found:
        # إذا لم يتم العثور على أي مشكلة، ننسخ الملف الأصلي إلى مسار الإخراج
        shutil.copy(input_path, output_path)
        print("No issues found")
    else:
        # إذا تم العثور على مشاكل، نحفظ الملف المعالج
        if output_path.lower().endswith(('.xlsx', '.xls')):
            df_cleaned.to_excel(output_path, index=False)
        else:
            df_cleaned.to_csv(output_path, index=False, encoding='utf-8')
            
        print("Cleaned file saved") 

if __name__ == "__main__":
    main()
