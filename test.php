<html>
<body>
<p>	
	#! /bin/bash

	cat > vpl_execution <&lt;EEOOFF
	#! /bin/bash

	# ---------- PROGRAMS TESTED (WITHOUT EXTENSION) ---------
	prog1=<?php echo $_POST["fileName"]; ?>
	compiled=true

	# --------------------- STARTING GRADE -------------------
	grade=0

	# --------------------- GLOBAL VARIABLES -------------------

	declare -a RegexList=(<?php for ($x = 0; $x < $_POST["numOfRegex"]; $x++) {echo "'".$_POST["regex".($x+1)]."' ";} ?>)
	declare -a Comment=(<?php for ($x = 0; $x < $_POST["numOfRegex"]; $x++) {echo "'".$_POST["comment".($x+1)]."' ";} ?>)

	# --------------------- SETTING VALUES FOR GRADES -------------------
	compileGrade=<?php echo $_POST["compileGrade"]; ?>
	numberOfRegex=<?php echo $_POST["numOfRegex"]; ?>
	regexGrade=<?php echo $_POST["regexGrade"]; ?>
	numberOfTestCases=<?php echo $_POST["numOfTC"]; ?>
	testCasesGrade=<?php echo $_POST["TCGrade"]; ?>
	regex=regexGrade/numberOfRegex
	testCase=testCasesGrade/numberOfTestCases

	# ----------------- COMPILE STUDENT PROG  ----------------
	javac \${prog1}.java  &> grepLines.out 

	#--- if error, assign a mi&imal grade ---
	if ((\$? > 0)); then
	     echo "Comment :=>> Your program has compiler Errors. Use the Run command to help solve the errors."
	     cat grepLines.out
	     echo "Comment :=>> ------------"
	     compiled=false
	fi


	if grep '.*' \${prog1}.java    # ---- Added so the file cannot be evaluated empty and still get marks ----
	then                                   # ---- Added so the file cannot be evaluated empty and still get marks ----
	    if [ \${compiled} = true ] ; then
		grade=\$((grade+compileGrade)) #---adds what % you wanted to give for the program compiling ---
	    fi
	fi                                     # ---- Added so the file cannot be evaluated empty and still get marks ----

	# ----------- Remove comments from the code ---------------------
	cat \$prog1.java | sed 's://.*$::g' | sed '/\/\*\*/,/\*\// {s/.*\*\/.*//p; d}' > _\$prog1.java


	# ----------- TEST THE CODE FOR PARTICULAR PATTERNS -------------
	# ----------- TEST Code -------------
	c=0
	for reg in \${RegexList[*]}; do
	    if grep \$reg \${prog1}.java
	    then
		 grade=\$((grade+regex)) #--- adds what % you wanted to give for each regex ---
	    else
		echo "Comment :=>> You have not \${Comment[\$c]} in " \${prog1}.java
		echo "Comment :=>> ------------"
	    fi
	    ((c++))
	done

	
	<?php 
		for ($x = 0; $x < $_POST["numOfTC"]; $x++) {
			echo 'cat > data'.($x+1).'.txt <&lt;EOF';
			for ($y = 1; $y <= $_POST["numOfIn".($y)]; $y++) {
				echo $_POST["input-".($x+1)."-".($y)]." ";
			}
			echo "EOF";
		} 
	?>
	
	<?php 
		for ($x = 0; $x < $_POST["numOfTC"]; $x++) {
			echo "cat > data".($x+1).".out <&lt;EOF";
			echo $_POST["output".($x+1)]." ";
			echo "EOF";
		} 
	?>
	
	count=0                                      # ---- Added to give full marks if all test cases are passed -----
	if [ \${compiled} = true ] ; then
	    #---loops through the amount of test cases you specified at the top ---
	    for((i=1;i<=\$numberOfTestCases;i++)) 
	    do 
	       echo "Comment :=>> --------------------------------"
	       echo "Comment :=>> (TEST \$i)"
	       # ==============================================
	       # TEST i
	       # ==============================================
	       #--- run program, capture output, display to student ---
	       java \${prog1} < data\${i}.txt  &> user.out
	       cp user.out user.out.org
	       
	     
	       #--- remove non numbers and non minus
	       #cat user.out | sed 's/[^0-9\ -]*//g' > dummy.out
	       #mv dummy.out user.out
	       
	       # ----------- Remove comments from the code ---------------------
	       cat \$prog1.java | sed 's://.*$::g' | sed '/\/\*\*/,/\*\// {s/.*\*\/.*//p; d}' > _\$prog1.java
	       
	       #--- remove multiple spaces --- 
	       cat user.out | sed 's/  */ /g' > dummy.out
	       mv dummy.out user.out
	       
	       #--- remove blank lines ---
	       cat user.out | sed '/^\s*$/d' > dummy.out
	       mv dummy.out user.out
	       
	       #--- compute difference --- 
	       diff -y -w --ignore-all-space user.out data\${i}.out > diff.out
	       #echo "----- diff.out ------"
	       #cat diff.out
	       #echo "---------------------"
	       diff -y -w --ignore-all-space user.out data\${i}.out > diff.out
	       
	    
	       #--- reject if different ---
	       if ((\$? > 0)); then
		  echo "Comment :=>> Your output is incorrect."
	    
		  #--- display test file ---
		  echo "Comment :=>> Your program tested with:"
		  echo "<|--" 
		  cat data\${i}.txt
		  echo "--|>"
	    
		  echo "Comment :=>> ---------------"
		  echo "Comment :=>> Your output:"
		  echo "Comment :=>> ---------------"
		  echo "<|--"
		  cat user.out.org
		  echo "--|>"
		  echo ""
		  echo "Comment :=>> ---------------"
		  echo "Comment :=>> Expected output (only the numbers): "
		  echo "Comment :=>> ---------------"
		  echo "<|--"
		  cat data\${i}.out
		  echo "--|>"
		  
		  # --------------------- REWARD IF CORRECT OUTPUT -----------------
	       else
		  ((count++))                       # ---- Added to give full marks if all test cases are passed -----
		  #--- good output ---
		  echo "Comment :=>> Congrats, your output is correct."
		  echo "<|--"
		  cat user.out.org
		  echo "--|>"
		  grade=\$((grade+testCase)) #---adds value to grade based on what % you wanted to give for testcases---
	       fi
	    done
	fi

	if (( count == numberOfTestCases )); then   # ---- Added to give full marks if all test cases are passed -----
	    grade=100                               # ---- Added to give full marks if all test cases are passed -----
	    echo "Comment :=>> ---------------"
	    echo "Comment :=>> You have passed all the Test Cases so you have been awarded full marks!"
	    echo "Comment :=>> ---------------"
	fi

	if (( grade > 100 )); then
	    grade=100
	fi
	if (( grade < 1 )); then
	    grade=0
	fi

	echo "Grade :=>> \$grade"

	EEOOFF

	chmod +x vpl_execution

</p>
</body>
</html>
