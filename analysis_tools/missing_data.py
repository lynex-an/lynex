import sys
import pandas as pd
import shutil 

# 🛑 1. الحل النهائي لمشكلة الترميز (UnicodeEncodeError) 🛑
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def main():
    # 🛑 2. التحقق من استقبال 6 مُعاملات 🛑
    # 1: input_path, 2: columns_param, 3: threshold_param, 4: output_path, 5: mode_param
    if len(sys.argv) < 6: 
        print("Error: Missing parameters. Expected 6 arguments.")
        sys.exit(1)

    input_path = sys.argv[1]
    # 🎯 استلام المتغيرات الجديدة من PHP
    columns_param = sys.argv[2] 
    # threshold_param غير مستخدم حالياً، ولكنه محجوز للتوسع المستقبلي في خوارزميات إكمال أكثر تعقيداً
    output_path = sys.argv[4]
    mode_param = sys.argv[5] 

    # 3. تحميل البيانات
    try:
        if input_path.lower().endswith(('.xlsx', '.xls')):
            df = pd.read_excel(input_path)
        else:
            df = pd.read_csv(input_path, encoding='utf-8') 
            
    except Exception as e:
        print(f"File Load Error: {e}")
        sys.exit(1)

    # 4. منطق التحليل والمعالجة (البيانات الناقصة)
    df_cleaned = df.copy()
    issues_found = False
    
    # 4.1 معالجة مُعامل الأعمدة (Columns Parameter)
    if columns_param.lower() == 'all':
        # إذا كانت القيمة 'all'، فسنراجع جميع الأعمدة التي تحتوي على قيم ناقصة
        subset_cols = df_cleaned.columns[df_cleaned.isnull().any()].tolist()
    else:
        # إذا تم تحديد أعمدة، سنقوم بتنظيفها
        subset_cols = [col.strip() for col in columns_param.split(',')]
        
        # 💡 تحقق: التأكد من أن الأعمدة موجودة
        missing_cols_in_data = [col for col in subset_cols if col not in df_cleaned.columns]
        if missing_cols_in_data:
            print(f"Error: Columns not found in file: {', '.join(missing_cols_in_data)}")
            sys.exit(1)

    # 4.2 تطبيق معالجة القيم الناقصة على الأعمدة المحددة
    for col in subset_cols:
        if df_cleaned[col].isnull().any(): # إذا كان العمود يحتوي على قيم ناقصة
            issues_found = True
            
            # ⚠️ المنطق المُحسَّن للمعالجة ⚠️
            if df_cleaned[col].dtype in ['int64', 'float64']:
                # ملء الرقمي بالوسيط (Median) - أكثر أماناً من المتوسط
                median_val = df_cleaned[col].median()
                df_cleaned[col].fillna(median_val, inplace=True)
                
            elif df_cleaned[col].dtype == 'object':
                # ملء النصي بقيمة 'Unknown' أو 'N/A' (لبيانات Upwork، "Unknown" جيد)
                df_cleaned[col].fillna('Unknown', inplace=True)

    # 5. حفظ الملف والإخراج المبسط لـ PHP 
    if not issues_found:
        # إذا لم يتم العثور على أي مشكلة
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
