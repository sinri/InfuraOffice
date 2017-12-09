#!/usr/bin/env bash

echo "STARTED on " $(date +\%Y-\%m-\%d-\%H:\%M:\%S)

echo explosion
# modify these parameters
explosion_file_patterns=('/tmp/explosion/*.out')
explosion_left_tail_lines=10
explosion_keep_backup=1

explosion_file_pattern_index=0
while [ ${explosion_file_pattern_index} -lt ${#explosion_file_patterns[@]} ] ; do
    echo "Explosion File Pattern: " ${explosion_file_pattern_index} " as " ${explosion_file_patterns[$explosion_file_pattern_index]}
    explosion_files=(`find / -path "${explosion_file_patterns[$explosion_file_pattern_index]}"`)

    explosion_file_index=0;
    while [ ${explosion_file_index} -lt ${#explosion_files[@]} ]; do
        target_explosion_file=${explosion_files[$explosion_file_index]}
        echo "Begin to explode " ${target_explosion_file}

        # explosion
        if [ ${explosion_keep_backup} == 1 ]; then
            target_explosion_file_backup=${target_explosion_file}.$(date +\%Y\%m\%d-\%H\%M).bak
            sudo cp ${target_explosion_file} ${target_explosion_file_backup}
            echo "Backup to " ${target_explosion_file_backup}
        fi

        if [ ${explosion_left_tail_lines} -gt 0 ];then
            sudo tail -n ${explosion_left_tail_lines} ${target_explosion_file} > ${target_explosion_file}
        else
            sudo echo "" > ${target_explosion_file}
        fi

        echo "Finish exploding " ${target_explosion_file}

        explosion_file_index=`expr ${explosion_file_index} + 1`
    done

    explosion_file_pattern_index=`expr ${explosion_file_pattern_index} + 1`
done

echo "antiquity and zombie"
# modify these parameters
antiquity_file_patterns=(
    '/tmp/Antiquity/a.*.log'
    '/tmp/Antiquity/b.*.log'
);
antiquity_not_modified_days=3;

antiquity_newer_mt=$(date -d "${antiquity_not_modified_days} days ago" +%Y%m%d)" 23:59"

antiquity_file_pattern_index=0
while [ ${antiquity_file_pattern_index} -lt ${#antiquity_file_patterns[@]} ] ; do
    echo "Antiquity File Pattern: " ${antiquity_file_pattern_index} " as " ${antiquity_file_patterns[antiquity_file_pattern_index]}
    antiquity_files=(`find / ! -newermt "${antiquity_newer_mt}" -path "${antiquity_file_patterns[$antiquity_file_pattern_index]}"`)

    antiquity_file_index=0;
    while [ ${antiquity_file_index} -lt ${#antiquity_files[@]} ]; do
        target_antiquity_file=${antiquity_files[$antiquity_file_index]}
        echo "Begin to remove antiquity " ${target_antiquity_file}

        sudo rm -f "${target_antiquity_file}"

        antiquity_file_index=`expr ${antiquity_file_index} + 1`
    done

    antiquity_file_pattern_index=`expr ${antiquity_file_pattern_index} + 1`
done

echo "FINISHED on " $(date +\%Y-\%m-\%d-\%H:\%M:\%S)