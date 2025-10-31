import sys
import pandas as pd
import shutil 
import numpy as np # لعمليات الرياضيات المتقدمة إن لزم الأمر

# 🛑 1. الحل النهائي لمشكلة الترميز (UnicodeEncodeError) 🛑
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')

def main():
    # 🛑 2. التحقق من استقبال 6 مُعاملات 🛑
    # 1: input_path, 2: columns_param, 3: threshold_param (Factor), 4: output_path, 5: mode_param
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
        if input_path.lower().endswith(('.xlsx', '.xls')):
            df = pd.read_excel(input_path)
        else:
            df = pd.read_csv(input_path, encoding='utf-8') 
            
    except Exception as e:
        print(f"File Load Error: {e}")
        sys.exit(1)

    # 4. منطق التحليل والمعالجة (البيانات الشاذة - Outliers)
    df_cleaned = df.copy()
    issues_found = False
    
    # تحويل معامل الحساسية (Threshold) إلى رقم عشري (Factor)
    try:
        # إذا كانت القيمة 85 (الافتراضية من PHP)، نقسمها على 100 لتصبح 0.85
        # لكن لتطبيق طريقة IQR، سنستخدمها كـ "مُضاعف" (Factor) بدلاً من نسبة مئوية.
        # الإعداد القياسي هو 1.5، لذلك إذا لم يتم إرسال معامل، سنستخدم 1.5.
        # للتجربة الأولية، سنحول القيمة إلى رقم عشري (مثلاً 1.5)
        # إذا كانت القيمة من PHP تأتي كـ 85، سنفترض أن العميل يريد حساسية قياسية (1.5)
        if threshold_param == "85": # القيمة الافتراضية
            factor = 1.5 
        else:
            # افتراض أن المُعامل هو قيمة Factor مباشرةً
            factor = float(threshold_param)
            if factor <= 0: factor = 1.5 # تجنب القيمة الصفرية أو السالبة
    except ValueError:
        factor = 1.5 # القيمة الإحصائية القياسية

    
    # 4.1 معالجة مُعامل الأعمدة (Columns Parameter)
    if columns_param.lower() == 'all':
        # نختار فقط الأعمدة الرقمية التي يمكن تطبيق IQR عليها
        subset_cols = df_cleaned.select_dtypes(include=['number']).columns.tolist()
    else:
        # إذا تم تحديد أعمدة، نستخدمها ونتأكد من أنها رقمية
        subset_cols = [col.strip() for col in columns_param.split(',')]
        
        # تحقق: التأكد من أن الأعمدة موجودة ورقمية
        valid_cols = []
        for col in subset_cols:
            if col not in df_cleaned.columns:
                print(f"Error: Column '{col}' not found in file.")
                sys.exit(1)
            # 💡 نطبق تحليل القيم الشاذة فقط على الأعمدة الرقمية
            if df_cleaned[col].dtype in ['int64', 'float64']:
                valid_cols.append(col)
            # يمكن إضافة تنبيه في المستقبل إذا كان العمود غير رقمي
        
        subset_cols = valid_cols

    # 4.2 تطبيق معالجة القيم الشاذة (IQR Method)
    for col in subset_cols:
        
        # تخطي الأعمدة التي ليس بها قيم كافية للحساب
        if df_cleaned[col].nunique() < 4:
            continue
            
        # حساب Q1 و Q3 و IQR
        Q1 = df_cleaned[col].quantile(0.25)
        Q3 = df_cleaned[col].quantile(0.75)
        IQR = Q3 - Q1
        
        # تحديد الحدود باستخدام العامل (Factor) الذي تم استقباله
        lower_bound = Q1 - factor * IQR
        upper_bound = Q3 + factor * IQR
        
        # تحديد القيم الشاذة
        outliers = (df_cleaned[col] < lower_bound) | (df_cleaned[col] > upper_bound)
        
        if outliers.any():
            issues_found = True
            
            # استبدال القيم الشاذة بالوسيط (Median) - Imputation
            median_val = df_cleaned[col].median()
            df_cleaned.loc[outliers, col] = median_val
            
            # 💡 ملاحظة: يمكن إضافة خيار حذف الصفوف (Drop Rows) في المستقبل

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
